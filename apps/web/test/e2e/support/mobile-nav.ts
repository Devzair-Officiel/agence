import type { Locator, Page } from '@playwright/test'
import { expect } from '@playwright/test'

// Ouvre le menu mobile de manière déterministe.
//
// En mode `nuxt dev`, `document.getElementById('__nuxt').__vue_app__` peut être
// posé par `mount()` avant que Vue n'ait effectivement attaché ses listeners
// `@click` sur le DOM initial. Un premier clic Playwright peut alors être
// dispatché *sans* handler côté Vue : le DOM click event est bien émis, mais
// aucun listener ne le reçoit, `isOpen` ne bascule pas et le dialog n'est
// jamais rendu. Le test échoue alors sur `expect(dialog).toBeVisible()`.
//
// La cause n'est pas un problème d'animation, de teleport ou de scroll-lock,
// mais une course entre l'attachement des handlers Vue et la dispatch du clic.
// La correction observe l'état *contractuel* du composant — `aria-expanded` sur
// le bouton — et re-clique tant que ce basculement n'a pas eu lieu. Une fois
// le handler attaché, le premier clic suivant réussit ; la retry est ~0.
//
// L'intervalle interne de 500ms laisse largement le temps à Vue de réagir
// après un clic effectif (tick microtask + patch DOM), sans imposer d'attente
// fixe (pas de `waitForTimeout`).
export async function openMobileNavigation(page: Page): Promise<Locator> {
  const button = page.locator('button[aria-controls="mobile-navigation"]')
  await button.waitFor({ state: 'visible' })
  await expect(async () => {
    await button.click()
    await expect(button).toHaveAttribute('aria-expanded', 'true', { timeout: 500 })
  }).toPass({ timeout: 10_000, intervals: [100, 200, 500] })
  const dialog = page.locator('#mobile-navigation')
  await expect(dialog).toBeVisible()
  return dialog
}
