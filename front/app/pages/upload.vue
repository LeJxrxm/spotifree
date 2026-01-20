<script setup lang="ts">
import type { Artiste } from '~/types/Artiste'
import type { Album } from '~/types/Album'

const { data: artists } = await useAsyncData<Artiste[]>('artists-list', () => api.get('/api/artistes'))

const items = [{
  label: 'File Upload',
  icon: 'i-lucide-upload',
  slot: 'file'
}, {
  label: 'YouTube Link',
  icon: 'i-simple-icons-youtube',
  slot: 'youtube'
}]

const uploadedTrackId = ref<number | null>(null)
const isUploading = ref(false)
const isSaving = ref(false)
const selectedFile = ref<File | null>(null)

// YouTube upload
const youtubeUrl = ref('')
const isDownloading = ref(false)

// Step 2 Form Data
const fileTitle = ref('')
const selectedArtist = ref<Artiste | {label: string, id: number} | null>(null) // On stocke l'id
const selectedAlbum = ref<Album | {label: string, id: number}  | null>(null) // On stocke l'id

const createdArtists = ref<{ label: string, id: number }[]>([])
const artistOptions = computed(() => {
  const existing = (artists.value || []).map(a => ({ label: a.nom, id: a.id }))

  return [...existing, ...createdArtists.value]
})

const availableAlbums = ref<Album[]>([])
const createdAlbums = ref<{ label: string, id: number }[]>([])
const albumOptions = computed(() => {
  return [...availableAlbums.value, ...createdAlbums.value]
})

function onCreateArtist(name: string) {
    const newArtist = { label: name, id: -1 }
    createdArtists.value.push(newArtist)
    selectedArtist.value = newArtist
}

function onCreateAlbum(titre: string) {
    const newAlbum = { label: titre, id: -1 }
    createdAlbums.value.push(newAlbum)
    selectedAlbum.value = newAlbum
}

// When artist changes, fetch their albums if it's an existing artist
watch(selectedArtist, async (newVal) => {
  selectedAlbum.value = null
  availableAlbums.value = []
  createdAlbums.value = []

  if (newVal && typeof newVal === 'object' && 'id' in newVal) {
    try {
      const artistDetails = await api.get<Artiste>(`/api/artistes/${(newVal as Artiste).id}`)
      if (artistDetails.albums) {
        availableAlbums.value = artistDetails.albums.map((a: Album) => ({
          label: a.titre,
          id: a.id
        }))
      }
    } catch (e) {
      console.error("Failed to fetch artist albums", e)
    }
  }
})

async function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files && input.files[0]) {
    selectedFile.value = input.files[0]
    // Start upload immediately
    await uploadFile()
  }
}

const toast = useToast()
async function uploadFile() {
  if (!selectedFile.value) return
  isUploading.value = true

  // Set default title from filename
  fileTitle.value = selectedFile.value.name.replace(/\.[^/.]+$/, "")

  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('titre', fileTitle.value)

  try {
    const res = await api.post<{ id: number, message: string }>('/api/musiques', formData)
    uploadedTrackId.value = res.id
    toast.add({ title: 'File uploaded, please complete details', color: 'primary' })
  } catch (e) {
    console.error(e)
    // alert('Upload failed: ' + (e instanceof Error ? e.message : String(e)))
    toast.add({ title: 'Upload failed', color: 'error' })
    selectedFile.value = null // Reset so they can try again
  } finally {
    isUploading.value = false
  }
}

async function saveDetails() {
//   if (!uploadedTrackId.value) return
  isSaving.value = true

  const payload: any = {
      titre: fileTitle.value
  }

  // Handle Artist
  if (selectedArtist.value) {
     payload.artiste = selectedArtist.value.id > 0 ? selectedArtist.value.id : selectedArtist.value.label
  }

  if(selectedAlbum.value) {
   payload.album = selectedAlbum.value.id > 0 ? selectedAlbum.value.id : selectedAlbum.value.label
  }

  try {
    await api.patch(`/api/musiques/${uploadedTrackId.value}`, payload)
    toast.add({ title: 'Track saved successfully', color: 'green' })
    navigateTo('/')
  } catch (e) {
    console.error(e)
    alert('Save failed: ' + (e instanceof Error ? e.message : String(e)))
  } finally {
    isSaving.value = false
  }
}

async function downloadFromYouTube() {
  if (!youtubeUrl.value) return
  isDownloading.value = true

  const payload: any = {
    youtubeUrl: youtubeUrl.value
  }

  try {
    const res = await api.post<{ id: number, message: string, title: string, duration: number }>('/api/musiques/youtube', payload)
    uploadedTrackId.value = res.id
    
    // Set the title from YouTube if not already set
    if (!fileTitle.value && res.title) {
      fileTitle.value = res.title
    }
    
    toast.add({ title: 'Track downloaded, please complete details', color: 'primary' })
  } catch (e) {
    console.error(e)
    toast.add({ title: 'Download failed: ' + (e instanceof Error ? e.message : String(e)), color: 'error' })
    youtubeUrl.value = '' // Reset so they can try again
  } finally {
    isDownloading.value = false
  }
}
</script>

<template>
  <div class="p-6 max-w-2xl mx-auto min-h-screen text-gray-900 dark:text-white">
    <h1 class="text-3xl font-bold mb-8">Upload New Track</h1>

    <UCard class="bg-gray-100 dark:bg-neutral-900 border-gray-200 dark:border-neutral-800">
      <UTabs :items="items" class="w-full">
        <template #file="{ item }">
          <div class="space-y-5 py-4">
            <!-- Step 1: Upload (Visible if no ID yet) -->
            <div v-if="!uploadedTrackId" class="space-y-4">
              <div v-if="isUploading" class="text-center py-12">
                <UIcon name="i-lucide-loader-2" class="w-12 h-12 animate-spin text-primary-500 mb-2" />
                <p class="text-lg font-medium">Uploading file...</p>
              </div>
              <div
                v-else
                class="border-2 border-dashed border-gray-300 dark:border-neutral-700 rounded-lg p-12 text-center hover:border-green-500 transition-colors cursor-pointer relative bg-gray-50 dark:bg-neutral-800/30 group">
                <input type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="onFileChange" accept="audio/*" />
                <div class="flex flex-col items-center gap-3 pointer-events-none transition-transform group-hover:scale-105 duration-300">
                  <UIcon name="i-lucide-cloud-upload" class="w-12 h-12 text-gray-400 dark:text-neutral-400 group-hover:text-green-500" />
                  <p class="text-gray-500 dark:text-neutral-400 font-medium">
                    <span class="text-lg">Click or drag file here to upload</span>
                  </p>
                  <p class="text-xs text-gray-400 dark:text-neutral-500">Supported formats: MP3, WAV, FLAC</p>
                </div>
              </div>
            </div>

            <!-- Step 2: Metadata (Visible if ID exists) -->
            <div v-else class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
              <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 p-4 rounded-lg flex items-center gap-2">
                <UIcon name="i-lucide-check-circle" class="w-5 h-5 shrink-0" />
                <span class="font-medium">File uploaded! Please complete the track details.</span>
              </div>

              <div class="grid grid-cols-1 gap-4">
                <div class="space-y-2">
                  <UFormField label="Track Title">
                    <UInput class="w-full" v-model="fileTitle" placeholder="Track Title" />
                  </UFormField>
                </div>

                <div class="space-y-2">
                  <UFormField label="Artist">
                    <USelectMenu class="w-full" v-model="selectedArtist" :items="artistOptions" searchable create-item placeholder="Select or create artist" @create="onCreateArtist" />
                  </UFormField>
                </div>

                <div class="space-y-2">
                  <UFormField label="Album (Optional)">
                    <USelectMenu class="w-full" v-model="selectedAlbum" :items="albumOptions" searchable create-item placeholder="Select or create album" @create="onCreateAlbum" />
                  </UFormField>
                </div>
              </div>

              <UButton block size="xl" color="primary" variant="solid" :loading="isSaving" @click="saveDetails" class="mt-4 font-bold">Save Draft</UButton>
            </div>
          </div>
        </template>

        <template #youtube="{ item }">
          <div class="space-y-5 py-4">
            <!-- Step 1: Download from YouTube -->
            <div v-if="!uploadedTrackId" class="space-y-4">
              <div v-if="isDownloading" class="text-center py-12">
                <UIcon name="i-lucide-loader-2" class="w-12 h-12 animate-spin text-red-500 mb-2" />
                <p class="text-lg font-medium">Downloading from YouTube...</p>
                <p class="text-sm text-gray-500 mt-2">This may take a few minutes</p>
              </div>

              <div v-else class="space-y-4">
                <div class="space-y-2">
                  <UFormField label="YouTube URL" required>
                    <UInput 
                      v-model="youtubeUrl" 
                      icon="i-simple-icons-youtube"
                      placeholder="https://www.youtube.com/watch?v=..." 
                      type="url"
                      size="xl"
                      class="w-full"
                    />
                    <template #hint>
                      <span class="text-xs text-gray-500">Paste a YouTube video link. The audio will be extracted automatically.</span>
                    </template>
                  </UFormField>
                </div>

                <UButton 
                  block 
                  size="xl" 
                  color="red" 
                  variant="solid" 
                  :loading="isDownloading"
                  :disabled="!youtubeUrl"
                  @click="downloadFromYouTube" 
                  class="mt-4 font-bold"
                  icon="i-lucide-download"
                >
                  Download from YouTube
                </UButton>
              </div>
            </div>

            <!-- Step 2: Metadata Form (same as file upload) -->
            <div v-else class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
              <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 p-4 rounded-lg flex items-center gap-2">
                <UIcon name="i-lucide-check-circle" class="w-5 h-5 shrink-0" />
                <span class="font-medium">Track downloaded! Please complete the track details.</span>
              </div>

              <div class="grid grid-cols-1 gap-4">
                <div class="space-y-2">
                  <UFormField label="Track Title">
                    <UInput class="w-full" v-model="fileTitle" placeholder="Track Title" />
                  </UFormField>
                </div>

                <div class="space-y-2">
                  <UFormField label="Artist">
                    <USelectMenu class="w-full" v-model="selectedArtist" :items="artistOptions" searchable create-item placeholder="Select or create artist" @create="onCreateArtist" />
                  </UFormField>
                </div>

                <div class="space-y-2">
                  <UFormField label="Album (Optional)">
                    <USelectMenu class="w-full" v-model="selectedAlbum" :items="albumOptions" searchable create-item placeholder="Select or create album" @create="onCreateAlbum" />
                  </UFormField>
                </div>
              </div>

              <UButton block size="xl" color="primary" variant="solid" :loading="isSaving" @click="saveDetails" class="mt-4 font-bold">Save Draft</UButton>
            </div>
          </div>
        </template>
      </UTabs>
    </UCard>
  </div>
</template>
