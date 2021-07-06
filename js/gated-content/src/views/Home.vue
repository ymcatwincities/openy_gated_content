<template>
  <div class="home">
    <ParagraphHeadline
      v-if="isHeadlineEnabled"
    ></ParagraphHeadline>

    <PageHeader title="Dashboard"></PageHeader>

    <PersonalTrainingListing
      :featured="true"
      :viewAll="true"
      :limit="8"
      v-if="config.personal_training_enabled"
    />

    <div v-for="component in componentsOrder" :key="component">
      <VideoListing
        :featured="true"
        :viewAll="true"
        :limit="8"
        :title="config.components.gc_video.title"
        :sort="sortData('node', 'gc_video')"
        v-if="isActive('gc_video') && showOnCurrentIteration('gc_video', component)"
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
import BlogListing from '@/components/blog/BlogListing.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import EventListing from '@/components/event/EventListing.vue';
import PersonalTrainingListing from '@/components/personal-training/PersonalTrainingListing.vue';
import ParagraphHeadline from '@/components/ParagraphHeadline.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';
import { mapGetters } from 'vuex';
import PageHeader from '@/components/PageHeader.vue';

export default {
  name: 'Home',
  mixins: [SettingsMixin, FilterAndSortMixin],
  components: {
    PageHeader,
    BlogListing,
    VideoListing,
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
