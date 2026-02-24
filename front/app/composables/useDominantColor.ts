const colorCache = new Map<string, string>()

const FALLBACK_COLOR = 'rgb(82, 82, 91)'
const BUCKET_SIZE = 18
const SAMPLE_SIZE = 50

function rgbToHsl(r: number, g: number, b: number): [number, number, number] {
  r /= 255
  g /= 255
  b /= 255
  const max = Math.max(r, g, b)
  const min = Math.min(r, g, b)
  const l = (max + min) / 2
  if (max === min) return [0, 0, l]
  const d = max - min
  const s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
  let h = 0
  if (max === r) h = ((g - b) / d + (g < b ? 6 : 0)) / 6
  else if (max === g) h = ((b - r) / d + 2) / 6
  else h = ((r - g) / d + 4) / 6
  return [h, s, l]
}

export const useDominantColor = () => {
  const extractDominantColor = async (imageUrl: string): Promise<string> => {
    const cached = colorCache.get(imageUrl)
    if (cached) return cached

    return new Promise((resolve) => {
      const img = new Image()
      img.crossOrigin = 'Anonymous'

      const cleanup = () => {
        img.onload = null
        img.onerror = null
        img.src = ''
      }

      img.onload = () => {
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d', { willReadFrequently: true })

        if (!ctx) {
          cleanup()
          resolve(FALLBACK_COLOR)
          return
        }

        canvas.width = SAMPLE_SIZE
        canvas.height = SAMPLE_SIZE
        ctx.drawImage(img, 0, 0, SAMPLE_SIZE, SAMPLE_SIZE)

        try {
          const imageData = ctx.getImageData(0, 0, SAMPLE_SIZE, SAMPLE_SIZE)
          const data = imageData.data

          const bucketScores: Record<string, { count: number; score: number; r: number; g: number; b: number }> = {}

          for (let i = 0; i < data.length; i += 4) {
            const r = data[i]
            const g = data[i + 1]
            const b = data[i + 2]
            const a = data[i + 3]

            if (a < 128) continue
            if (r > 240 && g > 240 && b > 240) continue
            if (r < 20 && g < 20 && b < 20) continue

            const rKey = Math.round(r / BUCKET_SIZE) * BUCKET_SIZE
            const gKey = Math.round(g / BUCKET_SIZE) * BUCKET_SIZE
            const bKey = Math.round(b / BUCKET_SIZE) * BUCKET_SIZE
            const key = `${rKey},${gKey},${bKey}`

            const [, s, l] = rgbToHsl(r, g, b)

            // Penalize very dark (< 0.15) and very light (> 0.85) colors
            const lightnessWeight = (l >= 0.15 && l <= 0.85) ? 1.0 : 0.3

            // Boost saturated colors: score uses saturation as a multiplier (min 0.1 to not zero-out grays entirely)
            const saturationWeight = 0.1 + s * 0.9

            const pixelScore = saturationWeight * lightnessWeight

            if (!bucketScores[key]) {
              bucketScores[key] = { count: 0, score: 0, r: rKey, g: gKey, b: bKey }
            }
            bucketScores[key].count++
            bucketScores[key].score += pixelScore
          }

          let bestScore = 0
          let bestColor = FALLBACK_COLOR

          for (const bucket of Object.values(bucketScores)) {
            if (bucket.score > bestScore) {
              bestScore = bucket.score
              bestColor = `rgb(${bucket.r}, ${bucket.g}, ${bucket.b})`
            }
          }

          colorCache.set(imageUrl, bestColor)
          cleanup()
          resolve(bestColor)
        } catch (e) {
          console.warn('Error extracting color:', e)
          cleanup()
          resolve(FALLBACK_COLOR)
        }
      }

      img.onerror = () => {
        console.warn('Failed to load image for color extraction')
        cleanup()
        resolve(FALLBACK_COLOR)
      }

      img.src = imageUrl
    })
  }

  return {
    extractDominantColor
  }
}
