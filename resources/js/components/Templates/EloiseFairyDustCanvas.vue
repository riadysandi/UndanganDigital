<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = withDefaults(defineProps<{
    count?: number;
    className?: string;
}>(), {
    count: 36,
    className: ''
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
let animationFrameId: number | null = null;

interface SparkleParticle {
    x: number;
    y: number;
    baseSize: number;
    currentSize: number;
    speedY: number;
    swayFreq: number;
    swayAmp: number;
    swayOffset: number;
    twinkleSpeed: number;
    twinklePhase: number;
    rotation: number;
    rotationSpeed: number;
    imgIndex: number;
}

const sparkleSrcs = [
    '/images/templates/eloise/sparkle/orn-1.png',
    '/images/templates/eloise/sparkle/orn-2.png',
    '/images/templates/eloise/sparkle/orn-3.png',
    '/images/templates/eloise/sparkle/orn-4.png'
];

onMounted(() => {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Load images
    const images: HTMLImageElement[] = [];
    let loadedCount = 0;
    sparkleSrcs.forEach((src) => {
        const img = new Image();
        img.src = src;
        img.onload = () => {
            loadedCount++;
        };
        images.push(img);
    });

    let width = 0;
    let height = 0;

    const resize = () => {
        const parent = canvas.parentElement;
        if (parent) {
            width = canvas.width = parent.clientWidth || window.innerWidth;
            height = canvas.height = parent.clientHeight || window.innerHeight;
        } else {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }
    };

    window.addEventListener('resize', resize);
    resize();

    // Create particles
    const particles: SparkleParticle[] = [];
    const particleCount = props.count;

    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * (width || window.innerWidth),
            y: Math.random() * (height || window.innerHeight),
            baseSize: 12 + Math.random() * 16,
            currentSize: 14,
            speedY: -(0.25 + Math.random() * 0.55),
            swayFreq: 0.008 + Math.random() * 0.015,
            swayAmp: 0.6 + Math.random() * 1.2,
            swayOffset: Math.random() * Math.PI * 2,
            twinkleSpeed: 0.02 + Math.random() * 0.04,
            twinklePhase: Math.random() * Math.PI * 2,
            rotation: Math.random() * Math.PI * 2,
            rotationSpeed: (Math.random() - 0.5) * 0.02,
            imgIndex: Math.floor(Math.random() * sparkleSrcs.length)
        });
    }

    let time = 0;

    const render = () => {
        time++;
        ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];

            // Update position
            p.y += p.speedY;
            p.x += Math.sin(time * p.swayFreq + p.swayOffset) * p.swayAmp;
            p.rotation += p.rotationSpeed;
            p.twinklePhase += p.twinkleSpeed;

            // Twinkle calculation
            const rawTwinkle = (Math.sin(p.twinklePhase) + 1) / 2; // 0 to 1
            const opacity = 0.2 + rawTwinkle * 0.75;
            p.currentSize = p.baseSize * (0.8 + rawTwinkle * 0.4);

            // Wrap around edges
            if (p.y < -30) {
                p.y = height + 20;
                p.x = Math.random() * width;
            }
            if (p.x < -30) p.x = width + 20;
            if (p.x > width + 30) p.x = -20;

            const img = images[p.imgIndex];
            ctx.save();
            ctx.globalAlpha = opacity;
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rotation);

            // Soft starlight glow
            ctx.shadowColor = '#FFEAA7';
            ctx.shadowBlur = 10;

            if (img && img.complete && img.naturalWidth > 0) {
                const s = p.currentSize;
                ctx.drawImage(img, -s / 2, -s / 2, s, s);
            } else {
                // Fallback star if image loading
                ctx.fillStyle = '#FFE599';
                ctx.beginPath();
                ctx.arc(0, 0, p.currentSize / 4, 0, Math.PI * 2);
                ctx.fill();
            }

            ctx.restore();
        }

        animationFrameId = requestAnimationFrame(render);
    };

    render();

    onBeforeUnmount(() => {
        window.removeEventListener('resize', resize);
        if (animationFrameId !== null) {
            cancelAnimationFrame(animationFrameId);
        }
    });
});
</script>

<template>
    <canvas
        ref="canvasRef"
        class="eloise-fairy-dust-canvas"
        :class="props.className"
    ></canvas>
</template>

<style scoped>
.eloise-fairy-dust-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 15;
}
</style>
