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
      <EventListing
        :featured="true"
        :viewAll="true"
        :limit="8"
        :title="config.components.live_stream.title"
        :sort="sortData('eventinstance', 'live_stream')"
        v-if="isActive('live_stream') && showOnCurrentIteration('live_stream', component)"
      />
      <EventListing
        :title="config.components.virtual_meeting.title"
        :featured="true"
        :viewAll="true"
        :limit="8"
        :eventType="'virtual_meeting'"
        :sort="sortData('eventinstance', 'virtual_meeting')"
        v-if="isActive('virtual_meeting') && showOnCurrentIteration('virtual_meeting', component)"
      />
      <BlogListing
        :featured="false"
        :viewAll="true"
        :limit="8"
        :title="config.components.vy_blog_post.title"
        :sort="sortData('node', 'vy_blog_post')"
        v-if="isActive('vy_blog_post') && showOnCurrentIteration('vy_blog_post', component)"
        class="my-40-20"
      />
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import BlogListing from '@/components/blog/BlogListing.vue';
import EventListing from '@/components/event/EventListing.vue';
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
    BlogListing,
    DurationsListing,
    EventListing,
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
