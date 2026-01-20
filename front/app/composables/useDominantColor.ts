export const useDominantColor = () => {
  const extractDominantColor = async (imageUrl: string): Promise<string> => {
    return new Promise((resolve) => {
      let retryAttempted = false
      
      const processImage = (imgElement: HTMLImageElement) => {
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')
        
        if (!ctx) {
          resolve('rgb(220, 38, 38)') // fallback red
          return
        }
        
        // Redimensionner l'image pour améliorer les performances
        const size = 50
        canvas.width = size
        canvas.height = size
        
        ctx.drawImage(imgElement, 0, 0, size, size)
        
        try {
          const imageData = ctx.getImageData(0, 0, size, size)
          const data = imageData.data
          
          // Compter les couleurs
          const colorCounts: { [key: string]: number } = {}
          
          for (let i = 0; i < data.length; i += 4) {
            const r = data[i]
            const g = data[i + 1]
            const b = data[i + 2]
            const a = data[i + 3]
            
            // Ignorer les pixels transparents et trop clairs/sombres
            if (a < 128 || (r > 240 && g > 240 && b > 240) || (r < 20 && g < 20 && b < 20)) {
              continue
            }
            
            // Grouper les couleurs similaires (arrondir à 30)
            const rKey = Math.round(r / 30) * 30
            const gKey = Math.round(g / 30) * 30
            const bKey = Math.round(b / 30) * 30
            const key = `${rKey},${gKey},${bKey}`
            
            colorCounts[key] = (colorCounts[key] || 0) + 1
          }
          
          // Trouver la couleur la plus fréquente
          let maxCount = 0
          let dominantColor = '220,38,38' // fallback
          
          for (const [color, count] of Object.entries(colorCounts)) {
            if (count > maxCount) {
              maxCount = count
              dominantColor = color
            }
          }
          
          resolve(`rgb(${dominantColor})`)
        } catch (e) {
          console.warn('Error extracting color, using fallback:', e)
          resolve('rgb(220, 38, 38)') // fallback red
        }
      }
      
      const img = new Image()
      img.crossOrigin = 'Anonymous'
      
      img.onload = () => processImage(img)
      
      img.onerror = () => {
        // If CORS fails and we haven't retried yet, try without crossOrigin
        if (!retryAttempted) {
          retryAttempted = true
          console.warn('CORS failed, trying without crossOrigin')
          const img2 = new Image()
          img2.onload = () => processImage(img2)
          img2.onerror = () => {
            console.error('Failed to load image after retry')
            resolve('rgb(220, 38, 38)') // fallback red
          }
          img2.src = imageUrl
        } else {
          console.error('Failed to load image')
          resolve('rgb(220, 38, 38)') // fallback red
        }
      }
      
      img.src = imageUrl
    })
  }
  
  return {
    extractDominantColor
  }
}
