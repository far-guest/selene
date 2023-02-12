<template>
    <div>
        <template v-if="displayType == 1">
            <span v-if="value" class="d-block" style="margin: -7px 0;">
                <a :href="value" target="_blank">فایل <i class="fa fa-external-link"></i></a>
            </span>
            <slot name="empty" v-else></slot>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <strong v-html="field.title"></strong>
            </div>

            <div :class="frameClass">
                <span v-if="value">
                    <a :href="value" target="_blank">فایل <i class="fa fa-external-link"></i></a>
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
                    <input ref="file" type="file" @change="upload" style="display: none;"/>
                    <div class="mb-3" v-text="fileName" v-if="fileName"></div>
                    <div>
                        <button type="button" class="btn btn-primary" @click="$refs.file.click()">
                            <i class="fa fa-upload"></i>
                            <span>آپلود فایل</span>
                        </button>
                        <button type="button" class="btn btn-danger" @click="clearFile" v-if="fileName">
                            <i class="fa fa-remove"></i>
                            <span>حذف فایل</span>
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

    export default {
        mixins: [seleneType],
        data() {
            return {
                fileName: null,
                fileMime: null,
            }
        },
        methods: {
            upload(e) {
                const file = e.target.files[0];

                if (file) {
                    if (typeof FileReader === 'function') {
                        this.fileName = file.name;
                        this.fileMime = file.type;
                        this.values[this.field.name] = 'upload';
                    } else {
                        alert('Sorry, FileReader API not supported');
                    }
                }
            },
            clearFile() {
                this.fileName = '';
                this.fileMime = '';
                this.$refs.file.value = '';
                this.values[this.field.name] = null;
            },
            onSubmit() {
                if (this.values[this.field.name] == 'upload') {
                    this.values[this.field.name + '_file'] = this.$refs.file.files[0];
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
                if (this.values.hasOwnProperty(this.field.name) && this.values[this.field.name]) {
                    let temp = this.values[this.field.name];
                    this.fileName = temp.substring(temp.lastIndexOf('/') + 1);
                }
            }
        }
    }
</script>