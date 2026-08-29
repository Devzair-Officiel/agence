/**
 * optimize-portfolio.mjs
 *
 * Convertit les PNG de public/portfolio/ en WebP via ffmpeg (libwebp).
 *
 * Usage : node scripts/optimize-portfolio.mjs
 *
 * Stratégie :
 *   Overlays (transparent) : quality 85, compression_level 6, max 960×960 px.
 *   Images principales     : quality 88, compression_level 6, sans redim.
 *
 * Les PNG source ne sont PAS supprimés. Vérification alpha auto après
 * chaque overlay converti.
 */

import { execSync } from "node:child_process"
import { readdirSync, statSync } from "node:fs"
import { resolve, extname } from "node:path"
import { fileURLToPath } from "node:url"

const PORTFOLIO = resolve(fileURLToPath(new URL(".", import.meta.url)), "../public/portfolio")

const OVERLAY_PATTERNS = ["honey-jar", "plate", "phone", "calendar", "chechia"]

function isOverlay(filename) {
  return OVERLAY_PATTERNS.some((p) => filename.includes(p))
}

function getDimensions(filePath) {
  try {
    const out = execSync(
      `ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0 "${filePath}"`,
      { encoding: "utf8" },
    ).trim()
    const [w, h] = out.split(",").map(Number)
    return { w, h }
  } catch {
    return { w: 0, h: 0 }
  }
}

function getPixFmt(filePath) {
  try {
    return execSync(
      `ffprobe -v error -select_streams v:0 -show_entries stream=pix_fmt -of csv=p=0 "${filePath}"`,
      { encoding: "utf8" },
    ).trim()
  } catch {
    return "unknown"
  }
}

function fileSize(filePath) {
  try {
    return statSync(filePath).size
  } catch {
    return 0
  }
}

function fmt(bytes) {
  if (bytes >= 1_000_000) return `${(bytes / 1_000_000).toFixed(2)} MB`
  return `${(bytes / 1_000).toFixed(0)} KB`
}

function convert(srcPath, destPath, overlay) {
  const quality = overlay ? 85 : 88
  const vf = overlay
    ? `-vf "scale='min(960,iw)':'min(960,ih)':force_original_aspect_ratio=decrease"`
    : ""
  const cmd = [
    "ffmpeg -y",
    `-i "${srcPath}"`,
    vf,
    "-c:v libwebp",
    `-quality ${quality}`,
    "-compression_level 6",
    "-lossless 0",
    `"${destPath}"`,
  ]
    .filter(Boolean)
    .join(" ")

  execSync(cmd, { stdio: "pipe" })
}

const pngs = readdirSync(PORTFOLIO)
  .filter((f) => extname(f).toLowerCase() === ".png")
  .sort()

if (pngs.length === 0) {
  console.log("Aucun PNG trouvé dans public/portfolio/")
  process.exit(0)
}

const rows = []
console.log(`\nOptimisation de ${pngs.length} PNG → WebP\n`)

for (const filename of pngs) {
  const src = resolve(PORTFOLIO, filename)
  const destName = filename.replace(/\.png$/i, ".webp")
  const dest = resolve(PORTFOLIO, destName)
  const overlay = isOverlay(filename)

  const { w: wBefore, h: hBefore } = getDimensions(src)
  const sizeBefore = fileSize(src)

  process.stdout.write(`  ${filename} → ${destName} … `)

  try {
    convert(src, dest, overlay)
  } catch (err) {
    console.error(`\n  ERREUR ffmpeg : ${err.message}`)
    rows.push({
      before: filename,
      after: destName,
      dimBefore: `${wBefore}×${hBefore}`,
      dimAfter: "—",
      sizeBefore,
      sizeAfter: 0,
      alpha: overlay ? "❌ échec" : "—",
    })
    continue
  }

  const { w: wAfter, h: hAfter } = getDimensions(dest)
  const sizeAfter = fileSize(dest)

  let alphaStatus = "—"
  if (overlay) {
    const pixFmt = getPixFmt(dest)
    const hasAlpha = /a/.test(pixFmt)
    alphaStatus = hasAlpha ? "✓" : `⚠ ${pixFmt}`
    if (!hasAlpha) {
      console.warn(`\n  ⚠  Canal alpha ABSENT dans ${destName} (${pixFmt})`)
    }
  }

  const gain =
    sizeBefore > 0
      ? `${(((sizeBefore - sizeAfter) / sizeBefore) * 100).toFixed(1)}%`
      : "—"
  console.log(`ok  ${fmt(sizeAfter)}  -${gain}${overlay && alphaStatus === "✓" ? "  α✓" : ""}`)

  rows.push({
    before: filename,
    after: destName,
    dimBefore: `${wBefore}×${hBefore}`,
    dimAfter: `${wAfter}×${hAfter}`,
    sizeBefore,
    sizeAfter,
    alpha: alphaStatus,
  })
}

const totalBefore = rows.reduce((s, r) => s + r.sizeBefore, 0)
const totalAfter = rows.reduce((s, r) => s + r.sizeAfter, 0)
const totalGain =
  totalBefore > 0
    ? (((totalBefore - totalAfter) / totalBefore) * 100).toFixed(1)
    : "0"

console.log("\n" + "─".repeat(110))
console.log("RAPPORT — public/portfolio/")
console.log("─".repeat(110))

console.log(
  "| Fichier source                           | Fichier dest                             | Dim. avant  | Dim. après  | Poids avant | Poids après | Gain   | Alpha |",
)
console.log(
  "|------------------------------------------|------------------------------------------|-------------|-------------|-------------|-------------|--------|-------|",
)
for (const r of rows) {
  const gain =
    r.sizeBefore > 0
      ? `${(((r.sizeBefore - r.sizeAfter) / r.sizeBefore) * 100).toFixed(1)}%`
      : "—"
  console.log(
    `| ${r.before.padEnd(40)} | ${r.after.padEnd(40)} | ${r.dimBefore.padStart(11)} | ${r.dimAfter.padStart(11)} | ${fmt(r.sizeBefore).padStart(11)} | ${fmt(r.sizeAfter).padStart(11)} | ${gain.padStart(6)} | ${r.alpha.padEnd(5)} |`,
  )
}
console.log("─".repeat(110))
console.log(`Total avant : ${fmt(totalBefore)}`)
console.log(`Total après : ${fmt(totalAfter)}`)
console.log(`Gain total  : ${fmt(totalBefore - totalAfter)} (${totalGain}%)`)
console.log("─".repeat(110) + "\n")
