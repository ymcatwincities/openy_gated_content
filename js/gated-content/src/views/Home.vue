<template>
  <div class="home">
    <ParagraphHeadline
      v-if="isHeadlineEnabled"
    ></ParagraphHeadline>

    <PageHeader title="Dashboard"></PageHeader>

    <PersonalTrainingListing
      :viewAll="true"
      :limit="8"
      v-if="config.personal_training_enabled"
    />

    <div v-for="component in componentsOrder" :key="component">
      <CategoriesListing
        :viewAll="true"
        :title="config.components.categories.title"
        :sort="sortData('taxonomy_term')"
        :limit="8"
        v-if="isActive('categories') && showOnCurrentIteration('categories', component)"
      />
      <DurationsListing
        :viewAll="true"
        :limit="8"
        :title="config.components.duration.title"
        v-if="isActive('duration') && showOnCurrentIteration('duration', component)"
      />
      <InstructorsListing
        :viewAll="true"
        :limit="8"
        :title="config.components.instructors.title"
        v-if="isActive('instructors') && showOnCurrentIteration('instructors', component)"
      />
      <VideoListing
        :featured="true"
        :viewAll="true"
        :limit="8"
        :title="config.components.latest_content.title"
        :sort="sortData('node', 'gc_video')"
        v-if="isActive('latest_content') && showOnCurrentIteration('latest_content', component)"
      />
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import VideoListing from '@/components/video/VideoListing.vue';
import PersonalTrainingListing from '@/components/personal-training/PersonalTrainingListing.vue';
import ParagraphHeadline from '@/components/ParagraphHeadline.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';
import PageHeader from '@/components/PageHeader.vue';
import CategoriesListing from '@/components/category/CategoriesListing.vue';
import DurationsListing from '@/components/duration/DurationsListing.vue';
import InstructorsListing from '@/components/instructor/InstructorsListing.vue';

export default {
  name: 'Home',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: {
    PageHeader,
    VideoListing,
    DurationsListing,
    InstructorsListing,
    CategoriesListing,
    PersonalTrainingListing,
    ParagraphHeadline,
  },
  computed: {
    ...mapGetters([
      'isHeadlineEnabled',
    ]),
  },
  methods: {
    isActive(component) {
      return this.config.components[component].status;
    },
  },
};
</script>
