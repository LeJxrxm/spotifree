import type { Artiste } from './Artiste'
import type { Album } from './Album'

export type Musique = {
    id: number
    titre: string
    duree: number // seconds
    audioUrl: string
    trackNumber?: number
    
    // Relations
    artiste?: Artiste
    album?: Album
}
