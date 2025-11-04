<template>
  <Suspense>
    <template #default>
      <router-view :key="$route.fullPath.concat('-app-initialized').replace('/', '-')" />
    </template>
    <template #fallback>
      <div class="flex items-center justify-center min-h-screen">
        <div class="flex flex-col items-center gap-4">
          <div
            class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"
          ></div>
          <p class="text-sm text-muted-foreground">Carregando...</p>
        </div>
      </div>
    </template> 
  </Suspense>
</template>
<script lang="ts" setup> 
</script>
