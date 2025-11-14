<script setup lang="ts">
import { computed, ref } from 'vue';
import { DropdownMenuRoot, DropdownMenuTrigger } from 'reka-ui';
import DropdownMenuContent from '@/components/ui/dropdown-menu/DropdownMenuContent.vue';
import DropdownMenuItem from '@/components/ui/dropdown-menu/DropdownMenuItem.vue';
import Icon from '@/components/Icon.vue';
import { useDateFormatter } from '@/composables/useDateFormatter';

const props = defineProps<{
  selectedMonth: number;
  selectedYear: number;
}>();

const emit = defineEmits<{
  'update:selectedMonth': [month: number];
}>();

const { getMonthName } = useDateFormatter();

const isOpen = ref(false);

const months = computed(() => {
  return Array.from({ length: 12 }, (_, i) => ({
    value: i,
    label: getMonthName.value(props.selectedYear, i),
  }));
});

const selectedMonthName = computed(() => getMonthName.value(props.selectedYear, props.selectedMonth));

const handleSelectMonth = (month: number) => {
  emit('update:selectedMonth', month);
  isOpen.value = false;
};

const handleKeydown = (event: KeyboardEvent, month: number) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    handleSelectMonth(month);
  }
};

const handleTriggerKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.stopPropagation();
  }
};
</script>

<template>
  <DropdownMenuRoot v-model:open="isOpen">
    <DropdownMenuTrigger
      class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-sm font-semibold transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-hidden focus-visible:ring-2 focus-visible:ring-ring"
      :aria-label="`Select month, currently ${selectedMonthName}`"
      aria-haspopup="listbox"
      :aria-expanded="isOpen"
      @keydown="handleTriggerKeydown"
    >
      {{ selectedMonthName }}
      <Icon
        name="chevron-down"
        :class="`h-3 w-3 text-muted-foreground transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`"
        aria-hidden="true"
      />
    </DropdownMenuTrigger>

    <DropdownMenuContent
      role="listbox"
      :aria-label="`Select month, currently ${selectedMonthName}`"
      class="max-h-[280px] min-w-[160px] overflow-y-auto"
    >
      <DropdownMenuItem
        v-for="month in months"
        :key="month.value"
        role="option"
        :aria-selected="month.value === selectedMonth"
        :class="month.value === selectedMonth ? 'bg-accent/50 font-medium' : ''"
        @click="handleSelectMonth(month.value)"
        @keydown="handleKeydown($event, month.value)"
      >
        <span class="flex flex-1 items-center justify-between">
          {{ month.label }}
          <Icon
            v-if="month.value === selectedMonth"
            name="check"
            class="h-4 w-4 text-foreground"
            aria-hidden="true"
          />
        </span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenuRoot>
</template>
