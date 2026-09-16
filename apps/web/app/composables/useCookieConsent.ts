import { computed, readonly, ref } from "vue"

/**
 * Clé de stockage localStorage et version du schéma.
 * Incrémenter CONSENT_VERSION pour invalider les préférences stockées
 * et redemander le consentement si les catégories ou finalités changent.
 */
export const CONSENT_STORAGE_KEY = "dz_cookie_prefs"
export const CONSENT_VERSION = 1

/**
 * Passer à `true` dès qu'un service analytics ou marketing nécessitant
 * un consentement préalable est activé. Le bandeau automatique s'affiche
 * alors pour les nouveaux visiteurs.
 * État actuel : false — aucun traceur non essentiel n'est configuré.
 */
export const hasConsentRequiredServices = false as const

export type CookiePreferences = {
  readonly necessary: true
  analytics: boolean
  marketing: boolean
}

type StoredPreferences = {
  version: number
  necessary: true
  analytics: boolean
  marketing: boolean
  updatedAt: string
}

// ── Helpers de stockage (guards SSR) ─────────────────────────────────────────

const isClient = typeof window !== "undefined"

function readStorage(): StoredPreferences | null {
  if (!isClient) return null
  try {
    const raw = localStorage.getItem(CONSENT_STORAGE_KEY)
    if (!raw) return null
    const parsed = JSON.parse(raw) as StoredPreferences
    if (typeof parsed !== "object" || parsed === null) return null
    if (parsed.version !== CONSENT_VERSION) return null
    return parsed
  } catch {
    return null
  }
}

function writeStorage(prefs: CookiePreferences): void {
  if (!isClient) return
  const stored: StoredPreferences = {
    version: CONSENT_VERSION,
    necessary: true,
    analytics: prefs.analytics,
    marketing: prefs.marketing,
    updatedAt: new Date().toISOString(),
  }
  localStorage.setItem(CONSENT_STORAGE_KEY, JSON.stringify(stored))
}

export function clearConsentStorage(): void {
  if (!isClient) return
  localStorage.removeItem(CONSENT_STORAGE_KEY)
}

// ── État singleton au niveau du module ───────────────────────────────────────
//
// Même approche que useMobileNavigation : refs au niveau module.
// Jamais utilisé pendant le SSR — le composant est enveloppé dans <ClientOnly>.

const _stored = readStorage()

const _preferences = ref<CookiePreferences>(
  _stored
    ? { necessary: true, analytics: _stored.analytics, marketing: _stored.marketing }
    : { necessary: true, analytics: false, marketing: false },
)

const _hasSaved = ref<boolean>(_stored !== null)

const _isOpen = ref<boolean>(false)

const _pending = ref<CookiePreferences>({ necessary: true, analytics: false, marketing: false })

// ── Fonction interne de sauvegarde ───────────────────────────────────────────

function _save(prefs: CookiePreferences): void {
  const safe: CookiePreferences = { necessary: true, analytics: prefs.analytics, marketing: prefs.marketing }
  writeStorage(safe)
  _preferences.value = { ...safe }
  _hasSaved.value = true
}

// ── Composable public ────────────────────────────────────────────────────────

export function useCookieConsent() {
  const preferences = readonly(_preferences)

  const hasSavedPreferences = readonly(_hasSaved)

  const showBanner = computed(() => hasConsentRequiredServices && !_hasSaved.value)

  const isPreferencesOpen = readonly(_isOpen)

  /** Préférences en cours d'édition dans la modale — modifiable par le composant. */
  const pendingPreferences = _pending

  function openPreferences(): void {
    _pending.value = { ...(_preferences.value) }
    _isOpen.value = true
  }

  function closePreferences(): void {
    _isOpen.value = false
  }

  function acceptAll(): void {
    _save({ necessary: true, analytics: true, marketing: true })
    _isOpen.value = false
  }

  function rejectOptional(): void {
    _save({ necessary: true, analytics: false, marketing: false })
    _isOpen.value = false
  }

  /** Enregistre `pendingPreferences`, en forçant `necessary: true`. */
  function savePreferences(): void {
    _save({ necessary: true, analytics: _pending.value.analytics, marketing: _pending.value.marketing })
    _isOpen.value = false
  }

  function resetPreferences(): void {
    clearConsentStorage()
    _preferences.value = { necessary: true, analytics: false, marketing: false }
    _hasSaved.value = false
  }

  return {
    preferences,
    hasSavedPreferences,
    hasConsentRequiredServices,
    showBanner,
    isPreferencesOpen,
    pendingPreferences,
    openPreferences,
    closePreferences,
    acceptAll,
    rejectOptional,
    savePreferences,
    resetPreferences,
  }
}
