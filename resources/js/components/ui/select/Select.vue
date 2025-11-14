<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { SelectRoot, type SelectRootEmits, type SelectRootProps } from 'reka-ui'
import { useVModel } from '@vueuse/core'

const props = defineProps<SelectRootProps & {
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<SelectRootEmits>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
})
</script>

<template>
  <SelectRoot v-model="modelValue" v-bind="props">
    <slot />
  </SelectRoot>
</template>
