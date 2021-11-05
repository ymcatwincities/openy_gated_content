<template>
  <div class="gated-content-categories-page">
    <Modal v-if="showModal" @close="showModal = false" class="adjust-modal">
      <template v-slot:header>
        <h3>Settings</h3>
      </template>
      <template v-slot:body>
        <div class="sort">
          <h4>Sort order</h4>
          <div class="form-check" v-for="option in filterOptions" v-bind:key="option.value">
            <label :for="option.value">
              <input
                type="radio"
                :id="option.value"
                :value="option.value"
                autocomplete="off"
                v-model="preSelectedSort"
              >
              <span class="checkmark"></span>
              <span class="caption">{{ option.label }}</span>
            </label>
          </div>
        </div>
      </template>
      <template v-slot:footer>
        <button type="button" class="btn btn-outline-primary" @click="showModal = false">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" @click="applyFilters">Apply</button>
      </template>
    </Modal>

    <div class="gated-containerV2 my-40-20 px--20-10 title-wrapper">
      <div class="title cachet-book-32-28 text-gray">Instructors</div>
      <button type="button"
              class="adjust-button" @click="showModal = true">Filter</button>
    </div>

    <InstructorsListing
      :title="'none'"
      :sort="sortData('taxonomy_term')"
    />
  </div>
</template>

<script>
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';
import InstructorsListing from '@/components/instructor/InstructorsListing.vue';

export default {
  name: 'InstructorsListingPage',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: {
    InstructorsListing,
  },
  data() {
    return {
      DEFAULT_SORT: 'date_asc',
      filterOptions: [
        { value: 'title_asc', label: 'By title (A-Z)' },
        { value: 'title_desc', label: 'By title (Z-A)' },
      ],
    };
  },
};
</script>
