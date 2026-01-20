# Configuration du Splash Screen PWA

## Étapes pour ajouter un splash screen à votre PWA

### 1. Créer les icônes

Vous avez besoin d'une icône de base (idéalement 1024x1024px) pour générer toutes les tailles.

**Option A : Utiliser un générateur en ligne**
- [PWA Asset Generator](https://github.com/elegantapp/pwa-asset-generator)
- [RealFaviconGenerator](https://realfavicongenerator.net/)
- [PWABuilder Image Generator](https://www.pwabuilder.com/imageGenerator)

**Option B : Générer manuellement avec ImageMagick**
```bash
# Installer ImageMagick si nécessaire
sudo apt install imagemagick  # Linux
brew install imagemagick       # macOS

# Créer les icônes à partir d'une image source (logo.png)
convert logo.png -resize 192x192 public/icon-192x192.png
convert logo.png -resize 512x512 public/icon-512x512.png
convert logo.png -resize 180x180 public/apple-touch-icon.png
convert logo.png -resize 32x32 public/favicon-32x32.png
convert logo.png -resize 16x16 public/favicon-16x16.png
```

### 2. Créer les icônes avec fond (pour splash screen)

Le splash screen iOS utilise l'icône avec la couleur de fond définie dans le manifest.

```bash
# Créer une icône avec fond noir (matching background_color)
convert -size 512x512 xc:#000000 public/bg.png
convert public/bg.png logo.png -gravity center -composite public/icon-512x512.png
```

### 3. Ajouter des screenshots (optionnel mais recommandé)

Pour améliorer le splash screen sur iOS et Android, ajoutez des screenshots :

**Tailles recommandées pour iPhone :**
- iPhone 14 Pro Max : 1290×2796
- iPhone 14 Pro : 1179×2556
- iPhone 13/14 : 1170×2532
- iPhone SE : 750×1334

**Prendre des screenshots :**
```bash
# Dans Chrome DevTools
# 1. Ouvrir DevTools (F12)
# 2. Toggle device toolbar (Ctrl+Shift+M)
# 3. Sélectionner "iPhone 14 Pro" ou dimensions custom
# 4. Capture screenshot (Ctrl+Shift+P > "Capture screenshot")
# 5. Sauvegarder dans public/screenshots/
```

### 4. Structure des fichiers dans /public

```
public/
├── icon-192x192.png      # Icône PWA standard
├── icon-512x512.png      # Icône PWA grande taille
├── apple-touch-icon.png  # Icône iOS (180x180)
├── favicon-32x32.png     # Favicon navigateur
├── favicon-16x16.png     # Favicon navigateur
└── screenshots/          # Screenshots pour PWA
    ├── home.png
    └── player.png
```

### 5. Configuration déjà appliquée dans nuxt.config.ts

```typescript
pwa: {
  manifest: {
    name: 'BackingTracks',
    short_name: 'BackingTracks',
    theme_color: '#000000',        // Couleur de la barre d'état
    background_color: '#000000',   // Couleur du splash screen
    display: 'standalone',
    icons: [
      {
        src: '/icon-192x192.png',
        sizes: '192x192',
        type: 'image/png',
        purpose: 'any maskable'    // 'maskable' pour Android adaptive icon
      },
      {
        src: '/icon-512x512.png',
        sizes: '512x512',
        type: 'image/png',
        purpose: 'any maskable'
      }
    ]
  }
}
```

### 6. Tester le splash screen

**Sur Android (Chrome) :**
1. Ouvrir l'app en mode navigation privée
2. Menu > "Ajouter à l'écran d'accueil"
3. Fermer l'onglet
4. Ouvrir l'app depuis l'écran d'accueil
5. Le splash screen apparaît pendant le chargement

**Sur iOS (Safari) :**
1. Ouvrir l'app dans Safari
2. Partager > "Sur l'écran d'accueil"
3. Fermer Safari
4. Ouvrir l'app depuis l'écran d'accueil
5. Le splash screen iOS apparaît (icône + background_color)

### 7. Optimisation du splash screen iOS

Pour iOS, le splash screen est généré automatiquement à partir de :
- `apple-touch-icon.png` (180x180)
- `theme_color` et `background_color` du manifest

**Améliorer l'expérience iOS :**
```html
<!-- Déjà ajouté dans nuxt.config.ts -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
```

### 8. Splash screen personnalisé (optionnel)

Si vous voulez un splash screen plus élaboré avec texte ou logo stylisé :

```typescript
// public/splash-screens/
// Générer différentes tailles pour chaque appareil iOS
// apple-splash-2048-2732.jpg  (iPad Pro 12.9")
// apple-splash-1668-2388.jpg  (iPad Pro 11")
// apple-splash-1170-2532.jpg  (iPhone 14 Pro)
// etc.

// Dans nuxt.config.ts
app: {
  head: {
    link: [
      { 
        rel: 'apple-touch-startup-image', 
        href: '/splash-screens/apple-splash-1170-2532.jpg',
        media: '(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)'
      }
      // Ajouter pour chaque appareil...
    ]
  }
}
```

### 9. Vérifier la configuration

```bash
# Build et preview
npm run build
npm run preview

# Ouvrir Chrome DevTools
# Application > Manifest
# Vérifier que toutes les icônes sont présentes
# Lighthouse > Progressive Web App > Vérifier le score
```

### 10. Debug

Si le splash screen ne s'affiche pas :
1. Vérifier que les icônes sont bien dans /public/
2. Vérifier les chemins dans le manifest (DevTools > Application > Manifest)
3. Clear cache et désinstaller/réinstaller la PWA
4. Vérifier la console pour les erreurs 404
5. S'assurer que `background_color` et `theme_color` sont définis

## Ressources

- [PWA Manifest Generator](https://www.simicart.com/manifest-generator.html/)
- [PWA Asset Generator (CLI)](https://github.com/elegantapp/pwa-asset-generator)
- [iOS Splash Screen Guide](https://appsco.pe/developer/splash-screens)
- [Android Adaptive Icons](https://developer.android.com/guide/practices/ui_guidelines/icon_design_adaptive)
