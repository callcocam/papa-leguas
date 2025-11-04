<template>
  <RouterView :key="$route.fullPath.concat('-crud-index').replaceAll('/', '_')" />
</template>
<script setup lang="ts">
import { RouterView } from 'vue-router'
</script>