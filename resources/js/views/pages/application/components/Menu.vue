<template>
<div class="mb-15x">
  <nav :class="[$store.state.user.admin ? 'page-menu' : 'page-menu page-menu__user']">
    <ul>
      <li>
        <router-link :to="{name: 'applications-archive', params: { type: $props.type }}" v-if="type == 'archive'">
          <icon-arrow-left />
          <span>Zurück</span>
        </router-link>
        <router-link :to="{name: 'applications-current', params: { type: $props.type }}" v-else>
          <icon-arrow-left />
          <span>Zurück</span>
        </router-link>
      </li>
      <li v-if="$store.state.user.admin">
        <router-link :to="{name: 'application-edit', params: { type: $props.type, uuid: $props.uuid }}" :active-class="'is-active'">
          <icon-pencil />
          <span>Bearbeiten</span>
        </router-link>
      </li>
      <li>
        <router-link :to="{name: 'application-comments', params: { type: $props.type, uuid: $props.uuid }}" :active-class="'is-active'">
          <icon-bubble />
          <span v-if="comments.length == 0">Kommentar</span>
          <span v-else-if="comments.length == 1">Kommentar (1)</span>
          <span v-else-if="comments.length > 1">Kommentare ({{comments.length}})</span>
        </router-link>
      </li>
      <li>
        <router-link :to="{name: 'application-logs', params: { type: $props.type, uuid: $props.uuid }}" :active-class="'is-active'">
          <icon-list />
          <span>Protokoll</span>
        </router-link>
      </li>
      <li v-if="$store.state.user.admin">
        <a href="javascript:;" @click.prevent="$refs.dialogArchive.show()" v-if="$props.application.archive == 0">
          <icon-download />
          <span>Archivieren</span>
        </a>
        <a href="javascript:;" @click.prevent="$refs.dialogRestore.show()" v-else>
          <icon-download />
          <span>Wiederherstellen</span>
        </a>
      </li>
      <li v-if="$store.state.user.admin">
        <a href="" @click.prevent="$refs.dialogDestroy.show()">
          <icon-trash />
          <span>Löschen</span>
        </a>
      </li>
    </ul>
    <slot />
  </nav>
  <dialog-wrapper ref="dialogArchive">
    <template #message>
      <div><strong>Möchten Sie das Gesuch</strong><br>{{$props.application.project_title}}<br><strong>wirklich archivieren?</strong></div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="archive()">Ja, archivieren</a>
    </template>
  </dialog-wrapper>
  
  <dialog-wrapper ref="dialogRestore">
    <template #message>
      <div><strong>Möchten Sie das Gesuch</strong><br>{{$props.application.project_title}}<br><strong>wirklich wiederherstellen?</strong></div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="restore()">Ja, wiederherstellen</a>
    </template>
  </dialog-wrapper>

  <dialog-wrapper ref="dialogDestroy">
    <template #message>
      <div><strong>Möchten Sie das Gesuch</strong><br>{{$props.application.project_title}}<br><strong>wirklich löschen?</strong></div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="destroy()">Ja, löschen</a>
    </template>
  </dialog-wrapper>
</div>
</template>
<script>
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import IconArrowLeft from "@/components/ui/icons/ArrowLeft-sm.vue";
import IconPencil from "@/components/ui/icons/Pencil.vue";
import IconBubble from "@/components/ui/icons/Bubble.vue";
import IconList from "@/components/ui/icons/List.vue";
import IconTrash from "@/components/ui/icons/Trash.vue";
import IconDownload from "@/components/ui/icons/Download.vue";

export default {

  components: {
    DialogWrapper,
    IconArrowLeft,
    IconPencil,
    IconBubble,
    IconList,
    IconTrash,
    IconDownload
  },

  props: {
    type: String,
    uuid: String,
    application: Object,
  },

  data() {
    return {

      // Comments
      comments: [],

      // States
      isLoading: false,
      isFetched: false,
      
      // Routes
      routes: {
        fetch: '/api/application',
        fetchComments: '/api/application-comments',
        destroy: '/api/application',
        put: '/api/application/archive',
      },
    }
  },

  mounted() {
    this.fetchComments();
  },

  methods: {

    fetch() {
      this.isFetched = false;
      this.axios.get(`${this.routes.fetch}/${this.$route.params.uuid}`).then(response => {
        this.data = response.data;
        this.isFetched = true;
      });
    },
    
    fetchComments() {
      this.isFetched = false;
      this.axios.get(`${this.routes.fetchComments}/${this.$route.params.uuid}`).then(response => {
        this.comments = response.data.data;
        this.isFetched = true;
      });
    },

    destroy() {
      this.isLoading = true;
      this.axios.delete(`${this.routes.destroy}/${this.$props.uuid}`).then(response => {
        this.isLoading = false;
        this.redirect(this.$props.type);
      });
    },

    archive() {
      this.isLoading = true;
      this.axios.put(`${this.routes.put}/${this.$route.params.uuid}`, {archive: 1}).then(response => {
        this.isLoading = false;
        this.$refs.dialogArchive.hide();
        this.redirect(this.$props.type);
      });
    },

    restore() {
      this.isLoading = true;
      this.axios.put(`${this.routes.put}/${this.$route.params.uuid}`, {archive: 0}).then(response => {
        this.isLoading = false;
        this.$refs.dialogArchive.hide();
        this.redirect('aktuell');
      });
    },

    redirect(type) {
      if (type == 'archiv') {
        return this.$router.push({ name: 'applications-archive', params: {type: 'archiv', uuid: this.$props.uuid} });
      }
      return this.$router.push({ name: 'applications-current', params: {type: 'aktuell', uuid: this.$props.uuid} });
    }

  }
}
</script>