import Plugin from 'src/plugin-system/plugin.class';
import {tns} from 'tiny-slider';

export default class VariantListingGallery extends Plugin {
    slider = null;

    _init() {
        this.initializeSlider();
    }

    initializeSlider() {
        if (this.slider) {
           this.slider.destroy();
        }

        const sliderElement = this.el.querySelector('.list-slider');
        if (!sliderElement) {
            return false;
        }

        this.slider = tns({
            container: sliderElement,
            controlsContainer: this.el.querySelector('.slider-controls'),
            items: 1,
            slideBy: 'page',
            autoplay: false,
            controls: true,
            mode: 'gallery',
            center: true,
            lazyload: false,
            nav: false,
            loop: false,
        });

        this.el.querySelector('.list-slider')
            .addEventListener('click', this.handleArticleClick.bind(this));

        return true;
    }

    handleArticleClick(event) {
        const link = event.currentTarget.dataset.link;
        if (link) {
            window.location.href = link;
        }
    }
}
