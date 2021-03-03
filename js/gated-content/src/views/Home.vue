<template>
  <div class="home">
    <ParagraphHeadline
      v-if="isHeadlineEnabled"
    ></ParagraphHeadline>

    <PageHeader title="Dashboard"></PageHeader>

    <div v-for="component in componentsOrder" :key="component">
      <VideoListing
        :featured="true"
        :viewAll="true"
        :limit="8"
        :title="config.components.gc_video.title"
        v-if="isActive('gc_video') && showOnCurrentIteration('gc_video', component)"
      />
      <EventListing
        :featured="true"
        :viewAll="true"
        :limit="8"
        :msg="config.components.live_stream.empty_block_text"
        :title="config.components.live_stream.title"
        v-if="isActive('live_stream') && showOnCurrentIteration('live_stream', component)"
      />
      <EventListing
        :title="config.components.virtual_meeting.title"
        :featured="true"
        :viewAll="true"
        :limit="8"
        :eventType="'virtual_meeting'"
        :msg="config.components.virtual_meeting.empty_block_text"
        v-if="isActive('virtual_meeting') && showOnCurrentIteration('virtual_meeting', component)"
      />
      <BlogListing
        :featured="false"
        :viewAll="true"
        :limit="8"
        :title="config.components.vy_blog_post.title"
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
import ParagraphHeadline from '@/components/ParagraphHeadline.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { mapGetters } from 'vuex';
import PageHeader from '@/components/PageHeader.vue';

export default {
  name: 'Home',
  mixins: [SettingsMixin],
  components: {
    PageHeader,
    BlogListing,
    VideoListing,
    EventListing,
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
