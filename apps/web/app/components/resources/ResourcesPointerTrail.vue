<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue"

/**
 * Traînée décorative du hero Ressources.
 *
 * Les positions sont capturées au plus une fois par frame et la liste reste
 * volontairement courte. Le mouvement est réservé aux souris précises et
 * entièrement neutralisé lorsque l'utilisateur demande moins d'animations.
 */
interface PointerPosition {
  x: number
  y: number
}

const surface = ref<HTMLElement | null>(null)

const MAX_POINTS = 36
const POINT_SPACING = 18
const MAX_POINTS_PER_FRAME = 6

let nextPointId = 0
let enabled = false
let animationFrame: number | null = null
let pendingPointer: PointerPosition | null = null
let lastPoint: PointerPosition | null = null
let reducedMotionQuery: MediaQueryList | null = null

function addPoint(x: number, y: number) {
  const el = surface.value
  if (!el) return

  const id = nextPointId++
  const size = 46 + (id % 4) * 6
  const point = document.createElement("span")
  point.className = "resources-pointer-trail__point"
  point.style.left = `${x}px`
  point.style.top = `${y}px`
  point.style.width = `${size}px`
  point.style.height = `${size}px`
  point.addEventListener("animationend", () => point.remove(), { once: true })

  if (el.childElementCount >= MAX_POINTS) {
    el.firstElementChild?.remove()
  }
  el.append(point)
}

function drawPendingTrail() {
  animationFrame = null

  const el = surface.value
  const pointer = pendingPointer
  pendingPointer = null
  if (!enabled || !el || !pointer) return

  const rect = el.getBoundingClientRect()
  const current = {
    x: pointer.x - rect.left,
    y: pointer.y - rect.top,
  }

  const isInside =
    current.x >= 0 &&
    current.x <= rect.width &&
    current.y >= 0 &&
    current.y <= rect.height

  if (!isInside) {
    lastPoint = null
    return
  }

  if (!lastPoint) {
    addPoint(current.x, current.y)
    lastPoint = current
    return
  }

  const deltaX = current.x - lastPoint.x
  const deltaY = current.y - lastPoint.y
  const distance = Math.hypot(deltaX, deltaY)
  if (distance < POINT_SPACING) return

  const pointCount = Math.min(
    Math.floor(distance / POINT_SPACING),
    MAX_POINTS_PER_FRAME,
  )

  for (let index = 1; index <= pointCount; index += 1) {
    const progress = index / pointCount
    addPoint(
      lastPoint.x + deltaX * progress,
      lastPoint.y + deltaY * progress,
    )
  }
  lastPoint = current
}

function handlePointerMove(event: PointerEvent) {
  if (!enabled || event.pointerType !== "mouse") return

  pendingPointer = { x: event.clientX, y: event.clientY }
  if (animationFrame === null) {
    animationFrame = window.requestAnimationFrame(drawPendingTrail)
  }
}

function syncMotionPreference() {
  enabled = Boolean(reducedMotionQuery && !reducedMotionQuery.matches)

  if (!enabled) {
    surface.value?.replaceChildren()
    pendingPointer = null
    lastPoint = null
  }
}

onMounted(() => {
  reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)")
  syncMotionPreference()

  reducedMotionQuery.addEventListener("change", syncMotionPreference)
  window.addEventListener("pointermove", handlePointerMove, { passive: true })
  surface.value?.setAttribute("data-pointer-trail-ready", "true")
})

onBeforeUnmount(() => {
  window.removeEventListener("pointermove", handlePointerMove)
  reducedMotionQuery?.removeEventListener("change", syncMotionPreference)

  if (animationFrame !== null) {
    window.cancelAnimationFrame(animationFrame)
  }
  surface.value?.replaceChildren()
})
</script>

<template>
  <div
    ref="surface"
    class="resources-pointer-trail"
    aria-hidden="true"
  />
</template>

<style scoped>
.resources-pointer-trail {
  position: absolute;
  z-index: 0;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.resources-pointer-trail :deep(.resources-pointer-trail__point) {
  position: absolute;
  display: block;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(46, 134, 217, 0.22) 0 7%,
    rgba(46, 134, 217, 0.11) 18%,
    rgba(12, 91, 87, 0.055) 42%,
    transparent 70%
  );
  mix-blend-mode: multiply;
  transform: translate(-50%, -50%) scale(0.45);
  animation: resources-pointer-trail-fade 900ms ease-out forwards;
  will-change: opacity, transform;
}

@keyframes resources-pointer-trail-fade {
  0% {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.45);
  }
  16% {
    opacity: 1;
  }
  100% {
    opacity: 0;
    transform: translate(-50%, -50%) scale(1.35);
  }
}

@media (prefers-reduced-motion: reduce), (scripting: none) {
  .resources-pointer-trail {
    display: none;
  }

  .resources-pointer-trail :deep(.resources-pointer-trail__point) {
    animation: none;
  }
}
</style>
