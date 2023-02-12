<template>
    <div class="card">
        <div class="card-header" v-html="tool.title"></div>
        <div class="card-body">
            نام کاربری: appazmoon@16bit.info
            <br>
            کلمه عبور: mi400557
            <hr>
            <a href="https://console.push-pole.com/signup" class="btn btn-primary" target="_blank">
                <i class="fa fa-external-link"></i>
                <span>ورود به پوشه</span>
            </a>
        </div>
    </div>
</template>
<script>
    import ToolMixin from '../../mixins/tool'

    export default {
        mixins: [ToolMixin],
    }
</script>
