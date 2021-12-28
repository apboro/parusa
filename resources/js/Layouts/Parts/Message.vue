<template>
    <div class="page__message" :class="classFromAttributes">
        <slot/>
    </div>
</template>

<script>

import AttributeKeysToClass from "../../Mixins/AttributeKeysToClass";

export default {
    mixins: [AttributeKeysToClass],

    data: () => ({
        attributesClassPrefix: 'page__message-',
    }),
}
</script>
