/**
 * Setup Vitest — stubs des composants et composables Nuxt utilisés par les
 * composants sous test. Vitest ne fait pas passer les auto-imports Nuxt ;
 * on remplace ici uniquement ce qui n'a pas d'équivalent standard.
 */

import { config } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { vi } from "vitest"

const NuxtLinkStub = defineComponent({
  name: "NuxtLink",
  props: {
    to: { type: [String, Object], required: true },
  },
  setup(props, { slots }) {
    return () =>
      h(
        "a",
        {
          href: typeof props.to === "string" ? props.to : "#",
          "data-nuxt-link": "true",
        },
        slots.default?.(),
      )
  },
})

// Teleport et Transition sont stubés pour rester dans l'arbre du wrapper.
// La couverture Teleport→body est validée manuellement via le SSR de la page.
const TeleportStub = defineComponent({
  name: "Teleport",
  setup(_, { slots }) {
    return () => h("div", { "data-teleport": "true" }, slots.default?.())
  },
})

const TransitionStub = defineComponent({
  name: "Transition",
  setup(_, { slots }) {
    return () => slots.default?.()
  },
})

config.global.stubs = {
  NuxtLink: NuxtLinkStub,
  Teleport: TeleportStub,
  Transition: TransitionStub,
}

// Stubs Nuxt : composables auto-importés non résolus par Vitest.
Object.assign(globalThis, {
  useSeoMeta: vi.fn(),
})
