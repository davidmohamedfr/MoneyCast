<script setup lang="ts">
import { Button } from '@/components/ui/button'

const props = defineProps<{
  title: string
  description?: string
  actionLabel?: string
  actionHref?: string
}>()

const emit = defineEmits<{
  action: []
}>()

const handleAction = () => {
  if (props.actionHref) {
    window.location.href = props.actionHref
  } else {
    emit('action')
  }
}
</script>

<template>
  <div
    class="flex flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center"
  >
    <div class="mx-auto flex max-w-md flex-col items-center gap-2">
      <h3 class="text-lg font-semibold">{{ title }}</h3>
      <p v-if="description" class="text-sm text-muted-foreground">
        {{ description }}
      </p>
      <Button
        v-if="actionLabel"
        class="mt-4"
        variant="outline"
        @click="handleAction"
      >
        {{ actionLabel }}
      </Button>
    </div>
  </div>
</template>
