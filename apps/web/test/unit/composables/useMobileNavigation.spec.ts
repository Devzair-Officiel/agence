import { describe, expect, it, beforeEach } from "vitest"
import { useMobileNavigation } from "~/composables/useMobileNavigation"

describe("useMobileNavigation", () => {
  beforeEach(() => {
    document.documentElement.className = ""
    // Reset shared state between tests by ensuring the menu starts closed.
    const { isOpen, close } = useMobileNavigation()
    if (isOpen.value) close()
  })

  it("starts closed", () => {
    const { isOpen } = useMobileNavigation()
    expect(isOpen.value).toBe(false)
  })

  it("opens then closes and toggles the scroll lock class on <html>", async () => {
    const { open, close, isOpen } = useMobileNavigation()
    const trigger = document.createElement("button")
    document.body.appendChild(trigger)

    open(trigger)
    expect(isOpen.value).toBe(true)
    // watcher runs synchronously with immediate:true, but the isOpen change
    // triggers a fresh run. Await a microtask to let it flush.
    await Promise.resolve()
    expect(document.documentElement.classList.contains("is-scroll-locked")).toBe(
      true,
    )

    close()
    expect(isOpen.value).toBe(false)
    await Promise.resolve()
    expect(document.documentElement.classList.contains("is-scroll-locked")).toBe(
      false,
    )
  })

  it("toggle() flips the open state", () => {
    const { toggle, isOpen } = useMobileNavigation()
    const trigger = document.createElement("button")
    toggle(trigger)
    expect(isOpen.value).toBe(true)
    toggle()
    expect(isOpen.value).toBe(false)
  })
})
