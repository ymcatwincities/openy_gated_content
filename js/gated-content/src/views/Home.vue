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
      <DurationsListing
        :featured="true"
        :viewAll="true"
        :limit="4"
        :title="config.components.duration.title"
        v-if="isActive('duration') && showOnCurrentIteration('duration', component)"
      />
      <VideoListing
        :featured="true"
        :viewAll="true"
        :limit="4"
        :title="config.components.gc_video.title"
        :sort="sortData('node', 'gc_video')"
        v-if="isActive('gc_video') && showOnCurrentIteration('gc_video', component)"
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
import DurationsListing from '@/components/duration/DurationsListing.vue';

export default {
  name: 'Home',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: {
    PageHeader,
    VideoListing,
    DurationsListing,
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
