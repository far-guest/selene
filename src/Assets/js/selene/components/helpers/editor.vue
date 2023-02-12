<template>
    <div>
        <div class="ckeditor">
            <textarea :id="ckeditorId" v-text="value"></textarea>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            value: {
                type: String
            },
            id: {
                type: String,
                default: 'editor'
            },
            height: {
                type: String,
                default: '300px',
            },
            toolbar: {
                type: Array,
                default: () => [
                    {name: 'styles', items: ['Format', 'FontSize']},
                    {name: 'colors', items: ['TextColor', 'BGColor']},
                    {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline']},
                    {
                        name: 'paragraph',
                        items: ['BidiLtr', 'BidiRtl', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'NumberedList', 'BulletedList']
                    },
                    {name: 'insert', items: ['Table']},
                    {name: 'tools', items: ['Maximize']},
                    {name: 'document', items: ['Sourcedialog']},
                ]
            },
            language: {
                type: String,
                default: 'en'
            },
            extraplugins: {
                type: String,
                default: ''
            }
        },
        computed: {
            ckeditorId() {
                return this.id
            },
        },
        beforeUpdate() {
            if (this.value !== CKEDITOR.instances[this.ckeditorId].getData()) {
                this.$nextTick(() => CKEDITOR.instances[this.ckeditorId].setData(this.value));
            }
        },
        mounted() {
            const ckeditorConfig = {
                toolbar: this.toolbar,
                language: this.language,
                height: this.height,
                extraPlugins: this.extraplugins
            };
            CKEDITOR.replace(this.ckeditorId, ckeditorConfig);
            CKEDITOR.instances[this.ckeditorId].setData(this.value);
            CKEDITOR.instances[this.ckeditorId].on('change', () => {
                let ckeditorData = CKEDITOR.instances[this.ckeditorId].getData();

                if (ckeditorData !== this.value) {
                    this.$emit('input', ckeditorData)
                }
            })
        },
        destroyed() {
            if (CKEDITOR.instances[this.ckeditorId]) {
                CKEDITOR.instances[this.ckeditorId].destroy()
            }
        }
    }
</script>