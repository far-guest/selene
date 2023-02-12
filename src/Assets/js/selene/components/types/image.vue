<template>
  <div>
    <template v-if="displayType == 1">
            <span v-if="value" class="d-block" style="margin: -7px 0;">
                <img class="bg-white border mx-auto" alt="image" height="35" :src="value">
            </span>
      <slot name="empty" v-else></slot>
    </template>
    <div class="row" v-if="displayType == 2">
      <div class="col-md-3">
        <strong v-html="field.title"></strong>
      </div>

      <div :class="frameClass">
                <span v-if="value">
                    <img class="bg-white border img-fluid" alt="image" style="max-height: 200px" :src="value">
                </span>
        <slot name="empty" v-else></slot>
      </div>
    </div>
    <div class="form-group mb-0" :aria-labelledby="`__input__${field.name}_LBL`" v-if="displayType == 3">
      <div class="form-row">
        <label class="col-md-3 col-form-label"
               :id="`__input__${field.name}_LBL`"
               :for="`__input__${field.name}`"
               v-html="field.title"></label>

        <div :class="frameClass">
          <div class="mb-3" v-if="src">
            <vue-cropper ref="cropper"
                         :src="src"
                         :view-mode="1"
                         :auto-crop-area="1"
                         :img-style="{'max-height': '400px'}"
                         v-if="value == 'upload'">
            </vue-cropper>
            <img class="bg-white border img-fluid" alt="image" style="max-height: 200px" :src="src" v-else>
          </div>

          <div>
            <input ref="file" type="file" accept="image/*" @change="upload" style="display: none"/>
            <button type="button" class="btn btn-primary" @click="$refs.file.click()">
              <i class="fa fa-upload"></i>
              <span>آپلود تصویر</span>
            </button>
            <button type="button" class="btn btn-danger" @click="clearImage" v-if="src">
              <i class="fa fa-remove"></i>
              <span>حذف تصویر</span>
            </button>
          </div>

          <div class="invalid-feedback d-block mt-3" v-if="validationState === false"
               v-html="validationErrors"></div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import seleneType from '../../mixins/input'
import VueCropper from 'vue-cropperjs';

export default {
  mixins: [seleneType],
  components: {'vue-cropper': VueCropper},
  data() {
    return {
      src: null,
      fileName: null,
      fileMime: null,
    }
  },
  methods: {
    upload(e) {
      const file = e.target.files[0];

      if (file) {
        if (!file.type.includes('image/')) {
          alert('Please select an image file');
          return;
        }

        if (typeof FileReader === 'function') {
          const reader = new FileReader();

          reader.onload = (event) => {
            this.src = null;
            this.$nextTick(() => {
              this.fileName = file.name;
              this.fileMime = file.type;
              this.src = event.target.result;
              this.values[this.field.name] = 'upload';
              this.$refs.file.value = null;
            });
          };

          reader.readAsDataURL(file);
        } else {
          alert('Sorry, FileReader API not supported');
        }
      }
    },
    clearImage() {
      this.fileName = null;
      this.fileMime = null;
      this.src = null;
      this.values[this.field.name] = null;
    },
    onSubmit() {
      if (this.values[this.field.name] == 'upload') {
        return new Promise(resolve => {
          this.$refs.cropper.getCroppedCanvas().toBlob(blob => {
            blob.name = this.fileName;
            this.values[this.field.name + '_file'] = blob;
            resolve();
          }, this.mime)
        });
      } else {
        delete this.values[this.field.name + '_file'];
      }
    },
    setValidationErrors(errors) {
      let err = [];

      if (errors.hasOwnProperty(this.field.name)) {
        err = err.concat(errors[this.field.name]);
      }

      if (errors.hasOwnProperty(this.field.name + '_file')) {
        err = err.concat(errors[this.field.name + '_file']);
      }

      if (err.length > 0) {
        this.$nextTick(() => {
          this.validationErrors = err.length == 1 ? err[0] : err.map(a => '<div>' + a + '</div>').join('');
        })
      }
    }
  },
  created() {
    if (this.displayType == 3) {
      if (this.values.hasOwnProperty(this.field.name)) {
        this.src = this.values[this.field.name];
      }
    }
  }
}
</script>