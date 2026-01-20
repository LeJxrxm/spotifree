export function useMediaSession() {
  const updateMediaSession = (track: {
    titre: string
    artiste?: { nom: string }
    album?: { titre: string; coverUrl?: string }
    audioUrl: string
  }) => {
    if (!('mediaSession' in navigator)) {
      console.warn('Media Session API not supported')
      return
    }

    // Mise à jour des métadonnées
    navigator.mediaSession.metadata = new MediaMetadata({
      title: track.titre,
      artist: track.artiste?.nom || 'Unknown Artist',
      album: track.album?.titre || 'Unknown Album',
      artwork: track.album?.coverUrl
        ? [
            {
              src: prefixApiResource(track.album.coverUrl),
              sizes: '512x512',
              type: 'image/jpeg',
            },
          ]
        : [],
    })

    // Configuration des actions (contrôles sur écran verrouillé)
    navigator.mediaSession.setActionHandler('play', () => {
      const audio = document.querySelector('audio')
      audio?.play()
    })

    navigator.mediaSession.setActionHandler('pause', () => {
      const audio = document.querySelector('audio')
      audio?.pause()
    })

    navigator.mediaSession.setActionHandler('seekbackward', () => {
      const audio = document.querySelector('audio')
      if (audio) audio.currentTime = Math.max(0, audio.currentTime - 10)
    })

    navigator.mediaSession.setActionHandler('seekforward', () => {
      const audio = document.querySelector('audio')
      if (audio) audio.currentTime = audio.currentTime + 10
    })

    navigator.mediaSession.setActionHandler('previoustrack', () => {
      // Géré par le composant parent
      window.dispatchEvent(new CustomEvent('mediaSessionPrevious'))
    })

    navigator.mediaSession.setActionHandler('nexttrack', () => {
      // Géré par le composant parent
      window.dispatchEvent(new CustomEvent('mediaSessionNext'))
    })
  }

  const updatePlaybackState = (state: 'playing' | 'paused' | 'none') => {
    if ('mediaSession' in navigator) {
      navigator.mediaSession.playbackState = state
    }
  }

  const updatePosition = (duration: number, currentTime: number, playbackRate: number = 1.0) => {
    if ('mediaSession' in navigator && 'setPositionState' in navigator.mediaSession) {
      try {
        navigator.mediaSession.setPositionState({
          duration,
          playbackRate,
          position: currentTime,
        })
      } catch (error) {
        console.error('Error updating position state:', error)
      }
    }
  }

  return {
    updateMediaSession,
    updatePlaybackState,
    updatePosition,
  }
}
