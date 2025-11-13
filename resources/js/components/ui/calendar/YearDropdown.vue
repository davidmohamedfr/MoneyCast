<script setup lang="ts">
import { computed, ref } from 'vue';
import { DropdownMenuRoot, DropdownMenuTrigger } from 'reka-ui';
import DropdownMenuContent from '@/components/ui/dropdown-menu/DropdownMenuContent.vue';
import DropdownMenuItem from '@/components/ui/dropdown-menu/DropdownMenuItem.vue';
import Icon from '@/components/Icon.vue';

const props = defineProps<{
  selectedYear: number;
}>();

const emit = defineEmits<{
  'update:selectedYear': [year: number];
}>();

const isOpen = ref(false);
const yearRangeStart = ref(props.selectedYear - 5);

const years = computed(() => {
  const start = yearRangeStart.value;
  return Array.from({ length: 11 }, (_, i) => start + i);
});

const canShowEarlier = computed(() => yearRangeStart.value > 1900);
const canShowLater = computed(() => yearRangeStart.value + 10 < new Date().getFullYear() + 20);

const handleSelectYear = (year: number) => {
  emit('update:selectedYear', year);
  isOpen.value = false;
};

const handleShowEarlier = () => {
  yearRangeStart.value -= 10;
};

const handleShowLater = () => {
  yearRangeStart.value += 10;
};

const handleKeydown = (event: KeyboardEvent, year: number) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    handleSelectYear(year);
  } else if (event.key === 'Home') {
    event.preventDefault();
    handleSelectYear(years.value[0]);
  } else if (event.key === 'End') {
    event.preventDefault();
    handleSelectYear(years.value[years.value.length - 1]);
  } else if (event.key === 'PageUp') {
    event.preventDefault();
    const newYear = props.selectedYear - 5;
    if (newYear >= yearRangeStart.value) {
      handleSelectYear(newYear);
    } else {
      yearRangeStart.value = newYear - 5;
      handleSelectYear(newYear);
    }
  } else if (event.key === 'PageDown') {
    event.preventDefault();
    const newYear = props.selectedYear + 5;
    if (newYear <= yearRangeStart.value + 10) {
      handleSelectYear(newYear);
    } else {
      yearRangeStart.value = newYear - 5;
      handleSelectYear(newYear);
    }
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
      :aria-label="`Select year, currently ${selectedYear}`"
      aria-haspopup="listbox"
      :aria-expanded="isOpen"
      @keydown="handleTriggerKeydown"
    >
      {{ selectedYear }}
      <Icon
        name="chevron-down"
        :class="`h-3 w-3 text-muted-foreground transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`"
        aria-hidden="true"
      />
    </DropdownMenuTrigger>

    <DropdownMenuContent
      role="listbox"
      :aria-label="`Select year, currently ${selectedYear}`"
      class="max-h-[280px] min-w-[160px] overflow-y-auto"
    >
      <DropdownMenuItem
        v-if="canShowEarlier"
        @click="handleShowEarlier"
        class="justify-center font-medium text-muted-foreground"
      >
        <Icon name="chevron-up" class="h-4 w-4" aria-hidden="true" />
        Earlier...
      </DropdownMenuItem>

      <DropdownMenuItem
        v-for="year in years"
        :key="year"
        role="option"
        :aria-selected="year === selectedYear"
        :class="year === selectedYear ? 'bg-accent/50 font-medium' : ''"
        @click="handleSelectYear(year)"
        @keydown="handleKeydown($event, year)"
      >
        <span class="flex flex-1 items-center justify-between">
          {{ year }}
          <Icon
            v-if="year === selectedYear"
            name="check"
            class="h-4 w-4 text-foreground"
            aria-hidden="true"
          />
        </span>
      </DropdownMenuItem>

      <DropdownMenuItem
        v-if="canShowLater"
        @click="handleShowLater"
        class="justify-center font-medium text-muted-foreground"
      >
        Later...
        <Icon name="chevron-down" class="h-4 w-4" aria-hidden="true" />
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenuRoot>
</template>
