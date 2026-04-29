/**
 * Fenix Foot - Audio Engine
 * Sistema de efeitos sonoros realistas
 */

const FenixAudio = (function () {
    'use strict';

    // AudioContext (Web Audio API)
    let ctx = null;
    let masterGain = null;
    let enabled = true;

    function getCtx() {
        if (!ctx) {
            ctx = new (window.AudioContext || window.webkitAudioContext)();
            masterGain = ctx.createGain();
            masterGain.gain.value = 0.75;
            masterGain.connect(ctx.destination);
        }
        // Resume if suspended (browser autoplay policy)
        if (ctx.state === 'suspended') ctx.resume();
        return ctx;
    }

    // ------------------------------------------------------------------ helpers
    function noise(duration = 0.05) {
        const c = getCtx();
        const bufferSize = c.sampleRate * duration;
        const buffer = c.createBuffer(1, bufferSize, c.sampleRate);
        const data = buffer.getChannelData(0);
        for (let i = 0; i < bufferSize; i++) data[i] = Math.random() * 2 - 1;
        const source = c.createBufferSource();
        source.buffer = buffer;
        return source;
    }

    function osc(type, freq, duration, gainVal = 0.3) {
        const c = getCtx();
        const o = c.createOscillator();
        const g = c.createGain();
        o.type = type;
        o.frequency.value = freq;
        g.gain.setValueAtTime(gainVal, c.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + duration);
        o.connect(g);
        g.connect(masterGain);
        o.start(c.currentTime);
        o.stop(c.currentTime + duration);
    }

    // ------------------------------------------------------------------ sounds
    function click() {
        if (!enabled) return;
        const c = getCtx();
        const o = c.createOscillator();
        const g = c.createGain();
        o.frequency.value = 1200;
        o.type = 'sine';
        g.gain.setValueAtTime(0.15, c.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + 0.08);
        o.connect(g);
        g.connect(masterGain);
        o.start();
        o.stop(c.currentTime + 0.08);
    }

    function whistle() {
        if (!enabled) return;
        const c = getCtx();

        // Apito longo + glide
        [0, 0.35, 0.7].forEach((delay) => {
            const o = c.createOscillator();
            const g = c.createGain();
            o.type = 'sine';
            o.frequency.setValueAtTime(2200, c.currentTime + delay);
            o.frequency.linearRampToValueAtTime(2800, c.currentTime + delay + 0.25);
            g.gain.setValueAtTime(0, c.currentTime + delay);
            g.gain.linearRampToValueAtTime(0.4, c.currentTime + delay + 0.05);
            g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + delay + 0.28);
            o.connect(g);
            g.connect(masterGain);
            o.start(c.currentTime + delay);
            o.stop(c.currentTime + delay + 0.3);
        });
    }

    function goal() {
        if (!enabled) return;
        const c = getCtx();

        // Kick drum
        const kick = c.createOscillator();
        const kickGain = c.createGain();
        kick.type = 'sine';
        kick.frequency.setValueAtTime(150, c.currentTime);
        kick.frequency.exponentialRampToValueAtTime(0.01, c.currentTime + 0.5);
        kickGain.gain.setValueAtTime(1, c.currentTime);
        kickGain.gain.exponentialRampToValueAtTime(0.001, c.currentTime + 0.5);
        kick.connect(kickGain);
        kickGain.connect(masterGain);
        kick.start(c.currentTime);
        kick.stop(c.currentTime + 0.5);

        // Torcida — noise filtrado
        const n = noise(2.5);
        const filter = c.createBiquadFilter();
        filter.type = 'bandpass';
        filter.frequency.value = 800;
        filter.Q.value = 0.5;
        const nGain = c.createGain();
        nGain.gain.setValueAtTime(0, c.currentTime);
        nGain.gain.linearRampToValueAtTime(0.35, c.currentTime + 0.3);
        nGain.gain.setValueAtTime(0.35, c.currentTime + 1.5);
        nGain.gain.linearRampToValueAtTime(0, c.currentTime + 2.5);
        n.connect(filter);
        filter.connect(nGain);
        nGain.connect(masterGain);
        n.start(c.currentTime);
        n.stop(c.currentTime + 2.5);

        // Melodia de celebração
        const notes = [523, 659, 784, 1047];
        notes.forEach((freq, i) => {
            osc('triangle', freq, 0.2, 0.25);
            // hack: spread via closure
            setTimeout(() => {}, i * 120);
        });
        // staggered via AudioContext scheduling
        const melody = [523, 659, 784, 1047];
        melody.forEach((freq, i) => {
            const t = c.currentTime + 0.1 + i * 0.13;
            const o = c.createOscillator();
            const g = c.createGain();
            o.type = 'triangle';
            o.frequency.value = freq;
            g.gain.setValueAtTime(0.25, t);
            g.gain.exponentialRampToValueAtTime(0.001, t + 0.18);
            o.connect(g);
            g.connect(masterGain);
            o.start(t);
            o.stop(t + 0.2);
        });
    }

    function crowd(intensity = 0.2) {
        if (!enabled) return;
        const c = getCtx();
        const n = noise(3);
        const filter = c.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.value = 1200;
        const g = c.createGain();
        g.gain.value = intensity;
        n.connect(filter);
        filter.connect(g);
        g.connect(masterGain);
        n.start();
        n.stop(c.currentTime + 3);
    }

    function toggle() {
        enabled = !enabled;
        if (masterGain) masterGain.gain.value = enabled ? 0.75 : 0;
        return enabled;
    }

    // ------------------------------------------------------------------ auto-bind buttons
    function bindButtons() {
        document.querySelectorAll('button, .btn, a.btn, input[type=submit], input[type=button]').forEach((el) => {
            if (!el.dataset.audiobound) {
                el.addEventListener('click', () => click());
                el.dataset.audiobound = '1';
            }
        });
    }

    // Observe DOM changes (dynamically added buttons)
    function observe() {
        const obs = new MutationObserver(() => bindButtons());
        obs.observe(document.body, { childList: true, subtree: true });
        bindButtons();
    }

    document.addEventListener('DOMContentLoaded', observe);

    // ------------------------------------------------------------------ public API
    return { click, whistle, goal, crowd, toggle, bindButtons };
})();

// Expose globally
window.FenixAudio = FenixAudio;
