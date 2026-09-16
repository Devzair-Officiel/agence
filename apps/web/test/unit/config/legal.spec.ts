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

  it("expose OVH SAS comme hébergeur", () => {
    expect(legalConfig.hostName).toBe("OVH SAS")
  })

  it("expose l'adresse hébergeur attendue", () => {
    expect(legalConfig.hostAddress).toContain("Roubaix")
  })

  it("expose une URL hébergeur valide commençant par https://", () => {
    expect(legalConfig.hostWebsite).toMatch(/^https:\/\//)
  })

  it("expose le nom de l'éditeur validé", () => {
    expect(legalConfig.publisherName).toBe("AURELIEN BOUDON")
  })

  it("expose l'adresse de l'éditeur validée", () => {
    expect(legalConfig.publisherAddress).toBe("39 avenue Edouard Herriot, Lyon")
  })

  it("expose le téléphone de l'éditeur validé", () => {
    expect(legalConfig.publisherPhone).toBe("06 87 76 37 84")
  })

  it("expose le directeur de la publication validé", () => {
    expect(legalConfig.publicationDirector).toBe("AURELIEN BOUDON")
  })

  it("aucun champ d'identité éditeur n'est null (DEV-LEGAL-1 levé)", () => {
    expect(legalConfig.publisherName).not.toBeNull()
    expect(legalConfig.publisherAddress).not.toBeNull()
    expect(legalConfig.publisherPhone).not.toBeNull()
    expect(legalConfig.publicationDirector).not.toBeNull()
  })

  it("expose le téléphone OVH SAS attendu", () => {
    expect(legalConfig.hostPhone).toBe("+33 9 72 10 10 07")
  })
})
