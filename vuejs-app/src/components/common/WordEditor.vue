<template>
  <!-- Documents saved before the new composer keep opening in the old editor (unchanged).
       New documents, and documents saved by the composer (data-doc-v="2"), use LetterComposer. -->
  <LegacyWordEditor
    v-if="isLegacy"
    v-bind="$attrs"
    :model-value="modelValue"
    @update:model-value="(v) => emit('update:modelValue', v)"
  />
  <LetterComposer
    v-else
    v-bind="$attrs"
    :model-value="modelValue"
    @update:model-value="(v) => emit('update:modelValue', v)"
  />
</template>

<script setup>
import { computed } from 'vue';
import LegacyWordEditor from './LegacyWordEditor.vue';
import LetterComposer from './LetterComposer.vue';
import { DOC_VERSION_MARK } from '@/utils/letterComposer';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  modelValue: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

const isLegacy = computed(() => {
  const v = (props.modelValue || '').trim();
  return v !== '' && !v.includes(DOC_VERSION_MARK);
});
</script>
