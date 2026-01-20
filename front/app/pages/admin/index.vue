<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})
</script>

<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
    <p>Welcome to the admin area.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <UCard>
            <template #header>
                <div class="font-bold">Artistes</div>
            </template>
            Manage your artists here.
            <template #footer>
                <UButton to="/admin/artistes" block>Go</UButton>
            </template>
        </UCard>
         <UCard>
            <template #header>
                <div class="font-bold">Albums</div>
            </template>
            Manage your albums here.
            <template #footer>
                <UButton to="/admin/albums" block>Go</UButton>
            </template>
        </UCard>
         <UCard>
            <template #header>
                <div class="font-bold">Musiques</div>
            </template>
            Manage your tracks here.
             <template #footer>
                <UButton to="/admin/musiques" block>Go</UButton>
            </template>
        </UCard>
         <UCard>
            <template #header>
                <div class="font-bold">Users</div>
            </template>
            Manage registered users.
             <template #footer>
                <UButton to="/admin/users" block>Go</UButton>
            </template>
        </UCard>
    </div>
  </div>
</template>
