<template>
<div>
  <site-header 
    :user="$store.state.user" 
    :view="'show'">
  </site-header>
  <site-main v-if="isFetched">
    <page-menu 
      :type="$route.params.type" 
      :uuid="$route.params.uuid"
      :application="data" 
      class="has-selection mb-20x"
    ></page-menu>
    <form @submit.prevent="submit" v-if="isFetched">
      <div class="application-action">
        <div>
          <div class="span-4 start-3">
            <button 
              type="submit" 
              :class="[hasErrors ? 'btn-primary is-small disabled' : 'btn-primary is-small']">
              Speichern
            </button>
          </div>
          <div class="span-4">
            <router-link 
              :to="{name: 'application-show', params: { type: $route.params.type, uuid: data.uuid }}"
              class="btn-secondary is-outline is-small">
              Abbrechen
            </router-link>
          </div>
        </div>
      </div>
      <application-wrapper>
        <application-grid>
          <div class="line-after">
            <h2>Organisation</h2>
            <application-row>
              <application-label :cls="'span-1'">Name</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.name" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Strasse</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.street" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Nr.</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.street_number" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">PLZ</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.zip" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Ort</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.city" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Webseite</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.website">
              </application-input>
            </application-row>
            <application-row class="pb-3x">
              <application-label :cls="'span-1'">Anrede Brief</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.salutation" placeholder="Sehr geehrte Damen und Herren">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Vorname</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.firstname" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Name</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.lastname" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Telefon</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.phone" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">E-Mail</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.email" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Rechtsform</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.legal_form" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row class="pb-3x">
              <application-label :cls="'span-1'">Bankkonto</application-label>
              <application-input class="span-3 mb-6x">{{ data.bank_account }}</application-input>
              <application-input :cls="'span-3 start-2 pb-4x'">
                Formulierung im Serienbrief<br><br>
                Wir bitten Sie, uns den Eingang des erwähnten Betrages, welchen wir Ihnen bis Ende<br><br>
                <input type="text" v-model="data.financial_periode" placeholder="[Monat Jahr]"><br>
                auf Ihr<br><br>
                <input type="text" v-model="data.financial_account" placeholder="[Kontoangaben]"><br>
                überweisen werden, umgehend schriftlich zu bestätigen.
              </application-input>
            </application-row>

            <h2>Dokumente</h2>
            <application-row>
              <div class="span-1"><label>Portrait</label></div>
              <div class="span-3 flex justify-between">
                <a :href="`/download/${data.uuid}/${data.file_portrait}`" class="anchor-download" target="_blank" title="Download Portrait" v-if="data.file_portrait">
                  {{data.file_portrait | truncate(30, '...')}}
                </a>
                <span v-else>–</span>
                <div class="flex justify-between">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadSending"
                    @vdropzone-success="uploadSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                    <a href="javascript:;" @click="uploadBefore('file_portrait')" class="icon-replace">
                      <icon-replace />
                    </a>
                  </vue-dropzone>
                  <a 
                    href="javascript:;" 
                    @click="deleteBefore('file_portrait')" 
                    class="icon-trash ml-3x" 
                    v-if="data.file_portrait">
                    <icon-trash />
                  </a>
                </div>
              </div>
            </application-row>
            <application-row>
              <div class="span-1"><label>Jahresbericht</label></div>
              <div class="span-3 flex justify-between">
                <a :href="`/download/${data.uuid}/${data.file_annual_report}`" class="anchor-download" target="_blank" title="Download Jahresbericht" v-if="data.file_annual_report">
                  {{data.file_annual_report | truncate(30, '...')}}
                </a>
                <span v-else>–</span>
                <div class="flex justify-between">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadSending"
                    @vdropzone-success="uploadSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                    <a href="javascript:;" @click="uploadBefore('file_annual_report')" class="icon-replace">
                      <icon-replace />
                    </a>
                  </vue-dropzone>
                  <a 
                    href="javascript:;" 
                    @click="deleteBefore('file_annual_report')" 
                    class="icon-trash ml-3x" 
                    v-if="data.file_annual_report">
                    <icon-trash />
                  </a>
                </div>
              </div>
            </application-row>
            <application-row>
              <div class="span-1"><label>Jahresrechnung</label></div>
              <div class="span-3 flex justify-between">
                <a :href="`/download/${data.uuid}/${data.file_annual_financial_report}`" class="anchor-download" target="_blank" title="Download Jahresrechnung" v-if="data.file_annual_financial_report">
                  {{data.file_annual_financial_report | truncate(30, '...')}}
                </a>
                <span v-else>–</span>
                <div class="flex justify-between">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadSending"
                    @vdropzone-success="uploadSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                    <a href="javascript:;" @click="uploadBefore('file_annual_financial_report')" class="icon-replace">
                      <icon-replace />
                    </a>
                  </vue-dropzone>
                  <a 
                    href="javascript:;" 
                    @click="deleteBefore('file_annual_financial_report')" 
                    class="icon-trash ml-3x" 
                    v-if="data.file_annual_financial_report">
                    <icon-trash />
                  </a>
                </div>
              </div>
            </application-row>
            <application-row>
              <div class="span-1"><label>Budget</label></div>
              <div class="span-3 flex justify-between">
                <a :href="`/download/${data.uuid}/${data.file_budget}`" class="anchor-download" target="_blank" title="Download Budget" v-if="data.file_budget">
                  {{data.file_budget | truncate(30, '...')}}
                </a>
                <span v-else>–</span>
                <div class="flex justify-between">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadSending"
                    @vdropzone-success="uploadSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                    <a href="javascript:;" @click="uploadBefore('file_budget')" class="icon-replace">
                      <icon-replace />
                    </a>
                  </vue-dropzone>
                  <a 
                    href="javascript:;" 
                    @click="deleteBefore('file_budget')" 
                    class="icon-trash ml-3x" 
                    v-if="data.file_budget">
                    <icon-trash />
                  </a>
                </div>
              </div>
            </application-row>
            <application-row>
              <div class="span-1"><label>Statuen</label></div>
              <div class="span-3 flex justify-between">
                <a :href="`/download/${data.uuid}/${data.file_bylaws}`" class="anchor-download" target="_blank" title="Download Statuen" v-if="data.file_bylaws">
                  {{data.file_bylaws | truncate(30, '...')}}
                </a>
                <span v-else>–</span>
                <div class="flex justify-between">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadSending"
                    @vdropzone-success="uploadSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                    <a href="javascript:;" @click="uploadBefore('file_bylaws')" class="icon-replace">
                      <icon-replace />
                    </a>
                  </vue-dropzone>
                  <a 
                    href="javascript:;" 
                    @click="deleteBefore('file_bylaws')" 
                    class="icon-trash ml-3x" 
                    v-if="data.file_bylaws">
                    <icon-trash />
                  </a>
                </div>
              </div>
            </application-row>
            <application-row>
              <div class="span-1"><label>Projekt</label></div>
              <div class="span-3 flex justify-between">
                <a :href="`/download/${data.uuid}/${data.file_project_description}`" class="anchor-download" target="_blank" title="Download Projekt" v-if="data.file_project_description">
                  {{data.file_project_description | truncate(30, '...')}}
                </a>
                <span v-else>–</span>
                <div class="flex justify-between">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadSending"
                    @vdropzone-success="uploadSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                    <a href="javascript:;" @click="uploadBefore('file_project_description')" class="icon-replace">
                      <icon-replace />
                    </a>
                  </vue-dropzone>
                  <a 
                    href="javascript:;" 
                    @click="deleteBefore('file_project_description')" 
                    class="icon-trash ml-3x" 
                    v-if="data.file_project_description">
                    <icon-trash />
                  </a>
                </div>
              </div>
            </application-row>
            <application-row>
              <div class="span-1"><label>KV</label></div>
              <div class="span-3 flex justify-between">
                <a :href="`/download/${data.uuid}/${data.file_project_estimated_costs}`" class="anchor-download" target="_blank" title="Download KV" v-if="data.file_project_estimated_costs">
                  {{data.file_project_estimated_costs | truncate(30, '...')}}
                </a>
                <span v-else>–</span>
                <div class="flex justify-between">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadSending"
                    @vdropzone-success="uploadSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                    <a href="javascript:;" @click="uploadBefore('file_project_estimated_costs')" class="icon-replace">
                      <icon-replace />
                    </a>
                  </vue-dropzone>
                  <a 
                    href="javascript:;" 
                    @click="deleteBefore('file_project_estimated_costs')" 
                    class="icon-trash ml-3x" 
                    v-if="data.file_project_estimated_costs">
                    <icon-trash />
                  </a>
                </div>
              </div>
            </application-row>

            <application-row v-for="file in data.files" :key="file.uuid">
              <div class="span-1"><label>{{file.title}}</label></div>
              <div class="span-3">
                <div class="flex justify-between">
                  <a :href="`/download/${data.uuid}/${file.name}`" class="anchor-download" target="_blank" :title="file.title">
                    {{file.name | truncate(30, '...')}}
                  </a>
                  <div class="flex justify-between">
                    <a 
                      href="javascript:;" 
                      @click="deleteFileBefore(file.uuid)" 
                      class="icon-trash">
                      <icon-trash />
                    </a>
                  </div>
                </div>
                <div v-if="file.comment" class="mt-2x pb-3x">
                  <span class="text-grey" v-if="file.user">{{file.user.full_name}}:<br></span>
                  {{file.comment}}
                </div>
              </div>
            </application-row>

            <!-- Upload -->
            <div v-if="hasUpload" class="mt-3x">
              <application-row v-if="dataUpload.name">
                <application-label :cls="'span-1'">Datei</application-label>
                <div class="span-3 flex justify-between">
                  <a :href="`/download/${data.uuid}/${dataUpload.name}`" class="anchor-download" target="_blank">
                    {{dataUpload.name | truncate(30, '...')}}
                  </a>
                  <a 
                    href="javascript:;" 
                    @click="deleteNewUpload()" 
                    class="icon-trash ml-3x">
                    <icon-trash />
                  </a>
                </div>
              </application-row>
              <application-row>
                <application-label :cls="'span-1'">Bezeichnung</application-label>
                <application-input :cls="'span-3'">
                  <input type="text" v-model="dataUpload.title" required @blur="validate($event)">
                </application-input>
              </application-row>
              <application-row>
                <application-label :cls="'span-1'">Kommentar</application-label>
                <application-input :cls="'span-3 pb-4x'">
                  <textarea v-model="dataUpload.comment" required @blur="validate($event)"></textarea>
                </application-input>
              </application-row>
              <div class="grid-cols-12">
                <div class="span-6">
                  <a href="javascript:;" class="btn-primary is-small" @click.prevent="storeFile()">Speichern</a>
                </div>
                <div class="span-6">
                  <a href="javascript:;" class="btn-secondary is-small is-outline">Abbrechen</a>
                </div>
              </div>
            </div>
            <div class="grid-cols-12 mt-3x" v-else>
              <div class="span-3 start-4">
                  <vue-dropzone
                    ref="dropzone"
                    id="dropzone"
                    :options="config"
                    @vdropzone-sending="uploadNewSending"
                    @vdropzone-success="uploadNewSuccess"
                    @vdropzone-complete="uploadComplete"
                    @vdropzone-max-files-exceeded="uploadMaxFilesExceeded"
                    :useCustomSlot=true
                  >
                  <a href="javascript:;" class="icon-plus-small">
                    <icon-plus />
                  </a>
                  </vue-dropzone>
              </div>
            </div>
            <!-- // Upload -->
          </div>
          <div class="line-after">
            <h2>Projekt</h2>
            <application-row>
              <application-label :cls="'span-1'">Titel</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.project_title" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-1'">Dauer</application-label>
              <application-input :cls="'span-3'">
                <input type="text" v-model="data.project_time" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <div class="span-1"><label>Begründung</label></div>
              <div class="span-3 pb-6x">{{data.justification_funds}}</div>
            </application-row>
            <application-row>
              <div class="span-1"><label>Begünstigte</label></div>
              <div class="span-3 pb-6x">{{data.project_beneficiaries}}</div>
            </application-row>
            <application-row>
              <div class="span-1"><label>Begünstigte Zürich</label></div>
              <div class="span-3">{{data.proportion_residents_benefit_program}}</div>
            </application-row>
          </div>
          <div>
            <h2>Projektkosten und Finanzierung</h2>
            <application-row>
              <application-label :cls="'span-3'">Total</application-label>
              <application-input :cls="'span-1'">
                <input type="text" v-model="data.project_cost_total" class="align-right" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-3'">Eigenleistungen</application-label>
              <application-input :cls="'span-1'">
                <input type="text" v-model="data.project_own_contribution" class="align-right" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row v-if="data.project_add_instit_final_total > 0">
              <div class="span-3"><label class="text-grey">Beiträge Dritter</label></div>
              <div class="span-1 flex justify-end text-grey">{{data.project_add_instit_final_total | currency}}</div>
            </application-row>
            <application-row v-if="data.project_add_instit_total_2 > 0">
              <div class="span-3 text-grey"><label class="text-grey">{{data.project_add_instit_2}}</label></div>
              <div class="span-1 flex justify-end text-grey">{{data.project_add_instit_total_2 | currency}}</div>
            </application-row>
            <application-row v-if="data.project_add_instit_total_3 > 0">
              <div class="span-3 text-grey"><label class="text-grey">{{data.project_add_instit_3}}</label></div>
              <div class="span-1 flex justify-end text-grey">{{data.project_add_instit_total_3 | currency}}</div>
            </application-row>
            <application-row v-if="data.project_add_instit_total_4 > 0">
              <div class="span-3 text-grey"><label class="text-grey">{{data.project_add_instit_4}}</label></div>
              <div class="span-1 flex justify-end text-grey">{{data.project_add_instit_total_4 | currency}}</div>
            </application-row>
            <application-row v-if="data.project_add_instit_total_5 > 0">
              <div class="span-3 text-grey"><label class="text-grey">{{data.project_add_instit_5}}</label></div>
              <div class="span-1 flex justify-end text-grey">{{data.project_add_instit_total_5 | currency}}</div>
            </application-row>
            <application-row>
              <application-label :cls="'span-3'">Budgetierter jährlicher Ertrag</application-label>
              <application-input :cls="'span-1'">
                <input type="text" v-model="data.project_income" class="align-right" required @blur="validate($event)">
              </application-input>
            </application-row>
            <application-row>
              <application-label :cls="'span-3'">Beantragter Beitrag</application-label>
              <application-input :cls="'span-1'">
                <input type="text" v-model="data.project_contribution_requested" class="align-right" required @blur="validate($event)">
              </application-input>
            </application-row>
            <!-- <application-row>
              <application-label :cls="'span-3'">Vorgeschlagener Betrag</application-label>
              <application-input :cls="'span-1'">
                <input type="text" v-model="data.project_contribution_approved_temporary" class="align-right" required @blur="validate($event)">
              </application-input>
            </application-row> -->
          </div>
        </application-grid>
      </application-wrapper>
    </form>
  </site-main>
  <dialog-wrapper ref="dialogDestroy">
    <template #message>
      <div><strong>Möchten Sie diese Datei wirklich löschen?</strong></div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="deleteUpload()">Ja, löschen</a>
    </template>
  </dialog-wrapper>
  <dialog-wrapper ref="dialogDestroyFile">
    <template #message>
      <div><strong>Möchten Sie diese Datei wirklich löschen?</strong></div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="deleteFile()">Ja, löschen</a>
    </template>
  </dialog-wrapper>
</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from "@/mixins/ErrorHandling";
import IconReplace from "@/components/ui/icons/Replace.vue";
import IconTrash from "@/components/ui/icons/Trash-sm.vue";
import IconPlus from "@/components/ui/icons/Plus-sm.vue";
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import SiteHeader from '@/views/layout/Header.vue';
import SiteMain from '@/views/layout/Main.vue';
import PageMenu from '@/views/pages/application/components/Menu.vue';
import ApplicationWrapper from '@/views/pages/application/components/Wrapper.vue';
import ApplicationGrid from '@/views/pages/application/components/Grid.vue';
import ApplicationRow from '@/views/pages/application/components/Row.vue';
import ApplicationLabel from '@/views/pages/application/components/Label.vue';
import ApplicationInput from '@/views/pages/application/components/Input.vue';
import vue2Dropzone from "vue2-dropzone";

export default {
  components: {
    NProgress,
    DialogWrapper,
    IconReplace,
    IconTrash,
    IconPlus,
    SiteHeader,
    SiteMain,
    PageMenu,
    ApplicationWrapper,
    ApplicationGrid,
    ApplicationRow,
    ApplicationLabel,
    ApplicationInput,
    vueDropzone: vue2Dropzone,
  },

  mixins: [ErrorHandling],

  props: {
    type: String
  },

  data() {
    return {
      
      // Model
      data: {},

      dataUpload: {
        uuid: this.$route.params.uuid,
        title: null,
        comment: null,
        name: null,
      },

      // Routes
      routes: {
        fetch: '/api/application',
        put: '/api/application',
        destroy: '/api/application',
        deleteUpload: '/api/application/file',
        storeFile: '/api/application-file/store',
        deleteFile: '/api/application-file/delete',
      },

      // States
      isFetched: true,
      isLoading: false,
      hasErrors: false,
      hasUpload: false,

      // Messages
      messages: {
        updated: 'Änderungen gespeichert!',
      },

      // Files
      fieldToDelete: null,
      uuidToDelete: null,

      // Dropzone config
      config: {
        url: "/api/application/file/upload",
        method: 'post',
        maxFilesize: 9,
        maxFiles: 100,
        createImageThumbnails: false,
        autoProcessQueue: true,
        acceptedFiles: '.pdf, .doc, .docx, .xls, .xlsx, .zip',
        previewTemplate: this.template(),
        headers: {
          'x-csrf-token': document.head.querySelector('meta[name="csrf-token"]').content
        }
      },

    };
  },

  created() {
    this.fetch();
    NProgress.configure({ showBar: false });
  },

  methods: {

    fetch() {
      NProgress.start();
      this.isFetched = false;
      this.axios.get(`${this.routes.fetch}/${this.$route.params.uuid}`).then(response => {
        this.data = response.data;
        this.isFetched = true;
        NProgress.done();
      });
    },

    submit() {
      NProgress.start();
      this.isFetched = true;
      this.axios.put(`${this.routes.put}/${this.$route.params.uuid}`, this.data).then(response => {
        this.$router.push({ name: 'application-show', params: {type: this.$route.params.type, uuid: this.data.uuid} });
        this.isFetched = false;
        NProgress.done();
      });
    },

    storeFile() {
      NProgress.start();
      this.axios.post(this.routes.storeFile, this.dataUpload).then(response => {
        this.data.files.push(response.data);
        this.hideUpload();
        NProgress.done();
      });
    },

    validate(event) {
      if (event.target.value.length > 0) {
        event.target.classList.remove('is-invalid');
        this.hasErrors = false;
        return;
      }
      event.target.classList.add('is-invalid');
      this.hasErrors = true;
    },

    showUpload() {
      this.hasUpload = true;
    },

    hideUpload() {
      this.dataUpload = {
        uuid: this.$route.params.uuid,
        title: null,
        comment: null,
        name: null,
      };
      this.hasUpload = false;
    },

    deleteFile() {
      NProgress.start();
      this.axios.delete(`${this.routes.deleteFile}/${this.uuidToDelete}`).then(response => {
        const index = this.data.files.findIndex(x => x.uuid === this.uuidToDelete);
        this.data.files.splice(index, 1);
        this.uuidToDelete = null;
        this.$refs.dialogDestroyFile.hide();
        NProgress.done();
      });
    },

    deleteUpload() {
      NProgress.start();
      this.axios.delete(`${this.routes.deleteUpload}/${this.$route.params.uuid}/${this.fieldToDelete}`).then(response => {
        this.data[this.fieldToDelete] = null;
        this.fieldToDelete = null;
        this.$refs.dialogDestroy.hide();
        NProgress.done();
      });
    },

    deleteFileBefore(uuid) {
      this.uuidToDelete = uuid;
      this.$refs.dialogDestroyFile.show();
    },

    deleteBefore(field) {
      this.fieldToDelete = field;
      this.$refs.dialogDestroy.show();
    },

    uploadBefore(field) {
      this.field_file = field;
    },

    uploadSending(file, xhr, formData) {
      NProgress.start();
      formData.append('uuid', this.$route.params.uuid);
      formData.append('field', this.field_file);
    },

    uploadSuccess(file, response) {
      let res = JSON.parse(file.xhr.response);
      this.data[this.field_file] = res.name;
      this.$refs.dropzone.removeFile(file);
      NProgress.done();
    },

    uploadNewSending(file, xhr, formData) {
      NProgress.start();
      formData.append('uuid', this.$route.params.uuid);
    },

    uploadNewSuccess(file, response) {
      let res = JSON.parse(file.xhr.response);
      this.dataUpload.name = res.name;
      this.showUpload();
      this.$refs.dropzone.removeFile(file);
      NProgress.done();
    },

    uploadComplete(file) {
      if (file.status == "error") {
        if (file.xhr !== undefined) {
          let res = JSON.parse(file.xhr.response);
          if (res.errors.file) {
            res.errors.file.forEach(error => console.log(error));
            this.$refs.dropzone.removeFile(file);
          }
        }
        else {
          if (file.accepted == false) {
            if (file.size > 9000000) {
              alert('image_exceeds_max_size');
            }
            else {
              alert('image_type_not_allowed');
            }

            this.$refs.dropzone.removeFile(file);
          }
        }
      }
      this.$refs.dropzone.removeFile(file);

    },

    uploadMaxFilesExceeded(file) {
      this.$refs.dropzone.removeAllFiles(true);
      alert('image_max_files_exceeded')
    },

    template: function () {
      return `<div></div>`;
    },
  },

};
</script>
