import { describe, expect, it } from 'vitest'
import { legalConfig } from '../../../app/config/legal'

describe("legalConfig — constantes légales Devzair", () => {
  it("expose le numéro SIREN attendu", () => {
    expect(legalConfig.siren).toBe("835 317 413")
  })

  it("expose le numéro SIRET attendu", () => {
    expect(legalConfig.siret).toBe("835 317 413 00036")
  })

  it("le SIRET commence par le SIREN (cohérence)", () => {
    const sirenDigits = legalConfig.siren.replace(/\s/g, "")
    const siretDigits = legalConfig.siret.replace(/\s/g, "")
    expect(siretDigits.startsWith(sirenDigits)).toBe(true)
  })

  it("expose le RCS attendu (Versailles)", () => {
    expect(legalConfig.rcs).toBe("RCS Versailles")
  })

  it("expose le code NAF attendu", () => {
    expect(legalConfig.naf).toBe("6201Z")
  })

  it("expose le numéro de TVA intracommunautaire attendu", () => {
    expect(legalConfig.vatNumber).toBe("FR28835317413")
  })

  it("le numéro TVA contient le SIREN", () => {
    const sirenDigits = legalConfig.siren.replace(/\s/g, "")
    expect(legalConfig.vatNumber).toContain(sirenDigits)
  })

  it("expose OVHcloud comme hébergeur", () => {
    expect(legalConfig.hostName).toBe("OVHcloud")
  })

  it("expose l'adresse hébergeur attendue", () => {
    expect(legalConfig.hostAddress).toContain("Roubaix")
  })

  it("expose une URL hébergeur valide commençant par https://", () => {
    expect(legalConfig.hostWebsite).toMatch(/^https:\/\//)
  })

  it("les champs d'identité éditeur sont null (validation légale en attente)", () => {
    expect(legalConfig.publisherName).toBeNull()
    expect(legalConfig.publisherAddress).toBeNull()
    expect(legalConfig.publisherPhone).toBeNull()
    expect(legalConfig.publicationDirector).toBeNull()
  })
})
