import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Contrôle d'accessibilité léger (Axe) sur `/design-preview`.
//
// Portée volontairement étroite :
//   - on cible la page interne de vérification visuelle, qui expose
//     l'ensemble des composants du design system ;
//   - on n'échoue **que** sur les violations sérieuses ou critiques ;
//   - on n'utilise aucun `disableRules` : si une violation apparaît, elle
//     doit être corrigée dans le composant.
//
// Ce test ne remplace pas un audit WCAG complet. Il vise à protéger
// contre les régressions les plus courantes (contraste, ARIA cassé,
// éléments non focusables, labels manquants).

test.describe('Axe (accessibility scan)', () => {
  test('no serious or critical violation on /design-preview', async ({ page }) => {
    await page.goto('/design-preview')

    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()

    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )

    if (blocking.length > 0) {
      // Log lisible côté CI : id, impact, cible, résumé.
      const lines = blocking.map(
        (v) =>
          `- [${v.impact}] ${v.id}: ${v.help}\n    ${v.nodes
            .map((n) => n.target.join(' '))
            .join('\n    ')}`,
      )
      console.log(`Axe blocking violations:\n${lines.join('\n')}`)
    }

    expect(blocking, 'Axe serious/critical violations').toEqual([])
  })
})
