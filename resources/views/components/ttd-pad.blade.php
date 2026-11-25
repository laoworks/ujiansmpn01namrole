@props(['name', 'tinggi' => 150])

@once
<script>
    function ttdPad() {
        return {
            data: '',
            kosong: true,
            lebar: 0,
            menggambar: false,

            mulai() {
                const c = this.$refs.kanvas;

                c.addEventListener('pointerdown', (e) => this.turun(e));
                c.addEventListener('pointermove', (e) => this.gerak(e));
                ['pointerup', 'pointerleave', 'pointercancel'].forEach((n) =>
                    c.addEventListener(n, () => this.angkat())
                );

                this.siapkan();
                if (window.ResizeObserver) {
                    new ResizeObserver(() => this.siapkan()).observe(c);
                }
            },

            siapkan() {
                const c = this.$refs.kanvas;
                const lebar = c.clientWidth;
                const tinggi = c.clientHeight;

                // Kotak yang sedang tersembunyi (lebar 0) disiapkan nanti, saat terlihat
                if (!lebar || (this.lebar && lebar === this.lebar)) return;

                const simpan = this.kosong ? null : this.data;
                const rasio = window.devicePixelRatio || 1;

                c.width = Math.round(lebar * rasio);
                c.height = Math.round(tinggi * rasio);
                this.lebar = lebar;

                const g = c.getContext('2d');
                g.scale(rasio, rasio);
                g.lineWidth = 2.4;
                g.lineCap = 'round';
                g.lineJoin = 'round';
                g.strokeStyle = '#111827';

                if (simpan) {
                    const img = new Image();
                    img.onload = () => g.drawImage(img, 0, 0, lebar, tinggi);
                    img.src = simpan;
                }
            },

            posisi(e) {
                const r = this.$refs.kanvas.getBoundingClientRect();
                return { x: e.clientX - r.left, y: e.clientY - r.top };
            },

            turun(e) {
                e.preventDefault();
                this.siapkan();
                try { this.$refs.kanvas.setPointerCapture(e.pointerId); } catch (x) {}

                const g = this.$refs.kanvas.getContext('2d');
                const p = this.posisi(e);

                this.menggambar = true;
                this.kosong = false;
                g.beginPath();
                g.moveTo(p.x, p.y);
                g.lineTo(p.x + 0.01, p.y + 0.01);
                g.stroke();
            },

            gerak(e) {
                if (!this.menggambar) return;
                e.preventDefault();

                const g = this.$refs.kanvas.getContext('2d');
                const p = this.posisi(e);

                g.lineTo(p.x, p.y);
                g.stroke();
                g.beginPath();
                g.moveTo(p.x, p.y);
            },

            angkat() {
                if (!this.menggambar) return;
                this.menggambar = false;
                this.data = this.kosong ? '' : this.$refs.kanvas.toDataURL('image/png');
            },

            hapus() {
                const c = this.$refs.kanvas;
                const g = c.getContext('2d');

                g.save();
                g.setTransform(1, 0, 0, 1, 0, 0);
                g.clearRect(0, 0, c.width, c.height);
                g.restore();

                this.kosong = true;
                this.data = '';
            },
        };
    }
</script>
@endonce

<div x-data="ttdPad()" x-init="mulai()" {{ $attributes }}>
    <div class="border-2 border-dashed border-gray-300 rounded-md bg-white">
        <canvas x-ref="kanvas" class="block w-full rounded-md"
                style="height: {{ $tinggi }}px; touch-action: none; cursor: crosshair;"></canvas>
    </div>

    <input type="hidden" name="{{ $name }}" :value="data">

    <div class="mt-2 flex flex-wrap items-center gap-3">
        <button type="button" @click="hapus()" class="text-sm text-gray-600 underline">Ulangi</button>
        <span class="text-xs text-gray-500">Gambar dengan jari atau mouse.</span>
        {{ $slot }}
    </div>
</div>