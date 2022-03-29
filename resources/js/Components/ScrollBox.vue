<template>
    <div class="scroll-box" :class="container_class">
        <div class="scroll-box__wrapper">
            <div class="scroll-box__scrollable" :class="scrollable_class">
                <slot></slot>
            </div>
        </div>
        <div class="scroll-box__vertical" v-show="thumbnail_vertical_show">
            <div class="scroll-box__vertical-thumb" :style="thumbnail_vertical_style"></div>
        </div>
        <div class="scroll-box__horizontal" v-show="thumbnail_horizontal_show">
            <div class="scroll-box__horizontal-thumb" :style="thumbnail_horizontal_style"></div>
        </div>
    </div>
</template>

<script>
    import ResizeObserver from 'resize-observer-polyfill';

    export default {
        props: {
            containerClass: {
                type: String,
                default: '',
            },
            scrollableClass: {
                type: String,
                default: '',
            },
            mode: String,
            initial: {
                type: Object,
                default: () => {
                    return {x: 0, y: 0}
                }
            },
        },

        computed: {
            thumbnail_vertical_show: function () {
                return this.thumbnail.vertical.show;
            },
            thumbnail_horizontal_show: function () {
                return this.thumbnail.horizontal.show;
            },
            container_class: function () {
                return this.containerClass;
            },
            scrollable_class: function () {
                return this.scrollableClass;
            },
            thumbnail_vertical_style: function () {
                return this.thumbnail.vertical.style;
            },
            thumbnail_horizontal_style: function () {
                return this.thumbnail.horizontal.style;
            },
        },

        data() {
            return {
                resizeObserver: {},
                containerEl: null,
                scrollableEl: null,
                verticalEl: null,
                horizontalEl: null,

                container: {
                    width: 0,
                    height: 0,
                },

                scrollable: {
                    width: 0,
                    height: 0,
                    top: 0,
                    left: 0,
                    enableVertical: false,
                    enableHorizontal: false,
                },

                thumbnail: {
                    vertical: {
                        top: 0,
                        height: 0,
                        show: false,
                        style: {
                            top: '0px',
                            height: '0px',
                        }
                    },
                    horizontal: {
                        left: 0,
                        width: 0,
                        show: false,
                        style: {
                            left: '0px',
                            width: '0px',
                        }
                    }
                },

                throttleInterval: 15,
                throttler: null,
                throttle: false,

                inertiaX: 0,
                inertiaY: 0,
                inertiaWorker: null,
            }
        },

        watch: {
            'scrollable.top': function () {
                this.$emit('changed', this.scrollable.left, this.scrollable.top);
            },
            'scrollable.left': function () {
                this.$emit('changed', this.scrollable.left, this.scrollable.top);
            },
        },

        methods: {
            refreshSizes(entries) {
                for (let i = 0; i < entries.length; i++) {
                    const entry = entries[i];
                    if (entry.target === this.containerEl) {
                        if (this.scrollable.enableVertical) {
                            this.container.height = entry.contentRect.height;
                        }
                        if (this.scrollable.enableHorizontal) {
                            this.container.width = entry.contentRect.width;
                        }
                    } else if (entry.target === this.scrollableEl) {
                        if (this.scrollable.enableVertical) {
                            this.scrollable.height = entry.contentRect.height;
                        }
                        if (this.scrollable.enableHorizontal) {
                            this.scrollable.width = entry.contentRect.width;
                        }
                    }
                }
                this.updateGeometry();
            },

            updateGeometry() {
                if (this.scrollable.enableVertical) {
                    const verticalRatio = this.container.height / this.scrollable.height;

                    // Make checks, corrections and calculate values
                    if (this.scrollable.height <= this.container.height) {
                        this.thumbnail.vertical.show = false;
                        this.scrollable.top = 0
                    } else {
                        if (this.scrollable.top < 0) {
                            this.scrollable.top = 0
                        }
                        if (this.scrollable.top > this.scrollable.height - this.container.height) {
                            this.scrollable.top = this.scrollable.height - this.container.height
                        }
                        this.thumbnail.vertical.show = true;
                        this.thumbnail.vertical.top = Math.round(this.scrollable.top * verticalRatio);
                        this.thumbnail.vertical.height = Math.round(this.container.height * verticalRatio);
                        this.thumbnail.vertical.style.top = this.thumbnail.vertical.top + 'px';
                        this.thumbnail.vertical.style.height = this.thumbnail.vertical.height + 'px';
                    }

                    // Update DOM
                    this.scrollableEl.style.top = -this.scrollable.top + "px";
                    if (this.thumbnail.vertical.show) {
                        this.$el.classList.add('with-scroll-box-vertical');
                    } else {
                        this.$el.classList.remove('with-scroll-box-vertical');
                    }
                }

                if (this.scrollable.enableHorizontal) {
                    const horizontalRatio = this.container.width / this.scrollable.width;

                    // Make checks, corrections and calculate values
                    if (this.scrollable.width <= this.container.width) {
                        this.thumbnail.horizontal.show = false;
                        this.scrollable.left = 0
                    } else {
                        if (this.scrollable.left < 0) {
                            this.scrollable.left = 0
                        }
                        if (this.scrollable.left > this.scrollable.width - this.container.width) {
                            this.scrollable.left = this.scrollable.width - this.container.width
                        }
                        this.thumbnail.horizontal.show = true;
                        this.thumbnail.horizontal.left = Math.round(this.scrollable.left * horizontalRatio);
                        this.thumbnail.horizontal.width = Math.round(this.container.width * horizontalRatio);
                        this.thumbnail.horizontal.style.left = this.thumbnail.horizontal.left + 'px';
                        this.thumbnail.horizontal.style.width = this.thumbnail.horizontal.width + 'px';
                    }

                    // Update DOM
                    this.scrollableEl.style.left = -this.scrollable.left + "px";
                    if (this.thumbnail.horizontal.show) {
                        this.$el.classList.add('with-scroll-box-horizontal');
                    } else {
                        this.$el.classList.remove('with-scroll-box-horizontal');
                    }
                }
            },

            onContainerScroll() {
                const scrollX = this.containerEl.scrollLeft;
                const scrollY = this.containerEl.scrollTop;
                this.scrollable.left += scrollX;
                this.scrollable.top += scrollY;
                this.containerEl.scrollTo(0, 0);
            },

            onMouseVerticalWheel(e) {
                e.stopPropagation();
                e.preventDefault();
                this.scrollable.top = this.scrollable.top + e.deltaY;
                this.updateGeometry();
            },

            onMouseHorizontalWheel(e) {
                e.stopPropagation();
                e.preventDefault();
                this.scrollable.left = this.scrollable.left + e.deltaY;
                this.updateGeometry();
            },

            falseFunction(e) {
                e.preventDefault();
                return false;
            },

            onMouseDragStartVertical(e) {
                this._coordinateY = e.pageY;
                this.$el.classList.add('is-dragging');
                this.$el.addEventListener('mousemove', this.onMouseDragProcessVertical);
                this.$el.addEventListener('mouseup', this.onMouseDragStopVertical);
                return false;
            },

            onMouseDragProcessVertical(e) {
                const offset = e.pageY - this._coordinateY;
                this._coordinateY = e.pageY;
                const ratio = this.scrollable.height / this.container.height;
                this.scrollable.top = this.scrollable.top + Math.round(offset * ratio);
                this.updateGeometry();
            },

            onMouseDragStopVertical() {
                this.$el.removeEventListener('mousemove', this.onMouseDragProcessVertical);
                this.$el.removeEventListener('mouseup', this.onMouseDragStopVertical);
                this.$el.classList.remove('is-dragging');
            },

            onMouseDragStartHorizontal(e) {
                this._coordinateX = e.pageX;
                this.$el.classList.add('is-dragging');
                this.$el.addEventListener('mousemove', this.onMouseDragProcessHorizontal);
                this.$el.addEventListener('mouseup', this.onMouseDragStopHorizontal);
                return false;
            },

            onMouseDragProcessHorizontal(e) {
                const offset = e.pageX - this._coordinateX;
                this._coordinateX = e.pageX;
                const ratio = this.scrollable.width / this.container.width;
                this.scrollable.left = this.scrollable.left + Math.round(offset * ratio);
                this.updateGeometry();
            },

            onMouseDragStopHorizontal() {
                this.$el.removeEventListener('mousemove', this.onMouseDragProcessHorizontal);
                this.$el.removeEventListener('mouseup', this.onMouseDragStopHorizontal);
                this.$el.classList.remove('is-dragging');
            },

            onVerticalRailClick(e) {
                e.stopPropagation();
                e.preventDefault();
                if (e.offsetY < this.thumbnail.vertical.top) {
                    this.scrollable.top = this.scrollable.top - this.container.height;
                    this.updateGeometry();
                } else if (e.offsetY > this.thumbnail.vertical.top + this.thumbnail.vertical.height) {
                    this.scrollable.top = this.scrollable.top + this.container.height;
                    this.updateGeometry();
                }
                return false;
            },

            onHorizontalRailClick(e) {
                e.stopPropagation();
                e.preventDefault();
                if (e.offsetX < this.thumbnail.horizontal.left) {
                    this.scrollable.left = this.scrollable.left - this.container.width;
                    this.updateGeometry();
                } else if (e.offsetX > this.thumbnail.horizontal.left + this.thumbnail.horizontal.width) {
                    this.scrollable.left = this.scrollable.left + this.container.width;
                    this.updateGeometry();
                }
                return false;
            },

            onTouchStart(e) {
                this.stopWorker();
                this._coordinateX = Math.floor(e.targetTouches[0].clientX);
                this._coordinateY = Math.floor(e.targetTouches[0].clientY);
                // this._coordinateX = Math.floor(e.touches[0].pageX);
                // this._coordinateY = Math.floor(e.touches[0].pageY);
                this.containerEl.addEventListener('touchmove', this.onTouchProcess);
                this.containerEl.addEventListener('touchend', this.onTouchStop);
                this.$el.classList.add('is-dragging');
                this.throttler = setInterval(() => {
                    this.throttle = false;
                }, this.throttleInterval);
                return true;
            },

            onTouchProcess(e) {
                e.preventDefault();
                e.stopPropagation();

                if (this.throttle) return false;

                const tX = Math.floor(e.targetTouches[0].clientX);
                const tY = Math.floor(e.targetTouches[0].clientY);
                const offsetX = tX - this._coordinateX;
                const offsetY = tY - this._coordinateY;
                this.inertiaX = offsetX;
                this.inertiaY = offsetY;
                this._coordinateX = tX;
                this._coordinateY = tY;
                if (offsetY !== 0 && this.scrollable.enableVertical) {
                    this.scrollable.top = this.scrollable.top - offsetY;
                }
                if (offsetX !== 0 && this.scrollable.enableHorizontal) {
                    this.scrollable.left = this.scrollable.left - offsetX;
                }
                if (offsetX !== 0 || offsetY !== 0) {
                    this.updateGeometry();
                }

                this.throttle = true;

                return false;
            },

            onTouchStop() {
                this.containerEl.ontouchmove = null;
                this.containerEl.removeEventListener('touchend', this.onTouchStop);
                this.$el.classList.remove('is-dragging');
                clearInterval(this.throttler);

                this.inertiaWorker = setInterval(() => {
                    if (this.inertiaX === 0 && this.inertiaY === 0) {
                        this.stopWorker();
                        return;
                    }
                    if (this.inertiaY !== 0 && this.scrollable.enableVertical) {
                        this.scrollable.top = this.scrollable.top - this.inertiaY;
                        this.inertiaY = this.inertiaY * 0.85;
                        if (Math.abs(this.inertiaY) < 1) this.inertiaY = 0;
                    }
                    if (this.inertiaX !== 0 && this.scrollable.enableHorizontal) {
                        this.scrollable.left = this.scrollable.left - this.inertiaX;
                        this.inertiaX = this.inertiaX * 0.85;
                        if (Math.abs(this.inertiaX) < 1) this.inertiaX = 0;
                    }
                    this.updateGeometry();
                }, this.throttleInterval);
            },

            stopWorker() {
                clearInterval(this.inertiaWorker);
            },

            bindEvents() {
                if (this.scrollable.enableVertical) {
                    // mouse wheel
                    this.scrollableEl.addEventListener('wheel', this.onMouseVerticalWheel);
                    this.verticalEl.parentElement.addEventListener('wheel', this.onMouseVerticalWheel);
                    // drag
                    this.verticalEl.addEventListener('dragstart', this.falseFunction);
                    this.verticalEl.onmousedown = this.onMouseDragStartVertical;
                    this.verticalEl.parentElement.addEventListener('click', this.onVerticalRailClick);
                }
                if (this.scrollable.enableHorizontal) {
                    // mouse wheel
                    this.horizontalEl.parentElement.addEventListener('wheel', this.onMouseHorizontalWheel);
                    // drag
                    this.horizontalEl.addEventListener('dragstart', this.falseFunction);
                    this.horizontalEl.onmousedown = this.onMouseDragStartHorizontal;
                    this.horizontalEl.parentElement.addEventListener('click', this.onHorizontalRailClick);

                }
                // touch
                this.containerEl.ontouchstart = this.onTouchStart;

                // container scroll
                this.containerEl.addEventListener('scroll', this.onContainerScroll);
            },

            unBindEvents() {
                // mouse wheel
                this.scrollableEl.removeEventListener('wheel', this.onMouseVerticalWheel);
                this.verticalEl.parentElement.removeEventListener('wheel', this.onMouseVerticalWheel);
                this.horizontalEl.parentElement.removeEventListener('wheel', this.onMouseHorizontalWheel);
                // drag
                this.verticalEl.removeEventListener('dragstart', this.falseFunction);
                this.verticalEl.onmousedown = null;
                this.verticalEl.parentElement.removeEventListener('click', this.onVerticalRailClick);
                this.horizontalEl.removeEventListener('dragstart', this.falseFunction);
                this.horizontalEl.onmousedown = null;
                this.horizontalEl.parentElement.removeEventListener('click', this.onHorizontalRailClick);
                // touch
                this.containerEl.ontouchstart = null;

                // container scroll
                this.containerEl.removeEventListener('scroll', this.onContainerScroll);
            },

            init() {
                // Get container and scrollable element
                this.containerEl = this.$el.children[0];
                this.scrollableEl = this.containerEl.children[0];
                this.verticalEl = this.$el.children[1].children[0];
                this.horizontalEl = this.$el.children[2].children[0];

                if (!this.containerEl) {
                    console.error('containerEl not set in ', this, ' found ', this.containerEl);
                    return;
                }
                if (!this.scrollableEl) {
                    console.error('scrollableEl not set in ', this);
                    return;
                }

                // Init mode
                const mode = String(this.mode);
                this.scrollable.enableVertical = (mode === 'both') || (mode === 'vertical');
                this.scrollable.enableHorizontal = (mode === 'both') || (mode === 'horizontal');

                // Set initial sizes
                if (this.scrollable.enableVertical) this.container.height = this.containerEl.clientHeight;
                if (this.scrollable.enableHorizontal) this.container.width = this.containerEl.clientWidth;
                if (this.scrollable.enableVertical) this.scrollable.height = this.scrollableEl.clientHeight;
                if (this.scrollable.enableHorizontal) this.scrollable.width = this.scrollableEl.clientWidth;

                // Create resize observer
                this.resizeObserver = new ResizeObserver(this.refreshSizes);

                // Bind resize events
                this.resizeObserver.observe(this.containerEl);
                this.resizeObserver.observe(this.scrollableEl);

                // Bind scroll events
                this.bindEvents();
            },

            destroy() {
                // Unbind resize events
                this.resizeObserver.unobserve(this.containerEl);
                this.resizeObserver.unobserve(this.scrollableEl);

                // Destroy resize observer
                delete this.resizeObserver;

                // Bind scroll events
                this.unBindEvents();
            }
        },

        mounted() {
            const initial = this.initial;
            this.init();
            if (initial.x !== 0 || initial.y !== 0) {
                this.scrollable.left = initial.x;
                this.scrollable.top = initial.y;
                this.updateGeometry();
            }
        },

        beforeDestroy() {
            this.destroy();
        }
    }
</script>

<style lang="scss">
.scroll-box {
    position: relative;
    flex-grow: 1;
    flex-shrink: 1;
    overflow: hidden;
    height: 100%;
}

.scroll-box.with-scroll-box-vertical {
    padding-right: 16px;
}

.scroll-box.with-scroll-box-horizontal {
    padding-bottom: 16px;
}

.scroll-box__wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
    box-sizing: border-box;
}

.scroll-box__scrollable {
    position: relative;
    box-sizing: border-box;
    vertical-align: top;
    margin: 0 !important;
    display: inline-block;
    min-width: 100%;
}

.scroll-box__vertical {
    position: absolute;
    top: 0;
    right: 3px;
    height: 100%;
    width: 4px;
    box-sizing: border-box;
    padding: 0;
    background-color: #f3f3f3;
}

.scroll-box__horizontal {
    position: absolute;
    bottom: 3px;
    left: 0;
    width: 100%;
    height: 4px;
    box-sizing: border-box;
    padding: 0;
    background-color: #f3f3f3;
}

.scroll-box__vertical-thumb, .scroll-box__horizontal-thumb {
    background-color: #5e94c9;
    position: relative;
    border-radius: 2px;
}

.scroll-box__vertical-thumb {
    width: 6px;
    left: -1px;
}

.scroll-box__horizontal-thumb {
    height: 6px;
    bottom: -1px;
}

.is-dragging * {
    -webkit-transition-property: unset;
    transition-property: unset;
    -webkit-transition-duration: 0s;
    transition-duration: 0s;
    -webkit-transition-timing-function: unset;
    transition-timing-function: unset;
    -webkit-transition-delay: 0s;
    transition-delay: 0s;
}

.is-dragging:after {
    content: "";
    width: 200vw;
    height: 200vh;
    position: relative;
    top: -100vh;
    left: -100vw;
    display: block;
}
</style>
