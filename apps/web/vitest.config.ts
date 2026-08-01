import { defineConfig } from "vitest/config"
import { fileURLToPath } from "node:url"
import { resolve, dirname } from "node:path"
import vue from "@vitejs/plugin-vue"

const currentDir = dirname(fileURLToPath(import.meta.url))

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: "happy-dom",
    include: ["test/**/*.{spec,test}.ts"],
    setupFiles: ["test/setup.ts"],
    globals: true,
  },
  resolve: {
    alias: {
      "~": resolve(currentDir, "app"),
      "@": resolve(currentDir, "app"),
    },
  },
})
