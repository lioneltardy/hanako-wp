import { Component } from 'hanako-ts/dist-legacy/Component';

type Direction = 'next' | 'prev';

export class Carousel extends Component {
  constructor() {
    super('Carousel', false);
  }

  public async init(): Promise<void> {
    await super.init();

    const carousels = Array.from(document.querySelectorAll<HTMLElement>('[data-hw-carousel]'));
    carousels.forEach((carousel) => this.initCarousel(carousel));

    this.success();
  }

  private initCarousel(carousel: HTMLElement): void {
    const slides = Array.from(carousel.querySelectorAll<HTMLElement>('.hw-carousel__item'));
    const indicators = Array.from(carousel.querySelectorAll<HTMLButtonElement>('[data-hw-carousel-to]'));
    const previousBtn = carousel.querySelector<HTMLButtonElement>('[data-hw-carousel-prev]');
    const nextBtn = carousel.querySelector<HTMLButtonElement>('[data-hw-carousel-next]');

    if (slides.length === 0) return;

    let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
    let isAnimating = false;

    slides.forEach((slide, index) => {
      if (index !== activeIndex) {
        slide.hidden = true;
        slide.setAttribute('aria-hidden', 'true');
      } else {
        slide.hidden = false;
        slide.setAttribute('aria-hidden', 'false');
      }
    });

    this.setActiveIndicator(indicators, activeIndex);

    const goTo = (nextIndex: number, direction: Direction) => {
      if (isAnimating || nextIndex === activeIndex) return;

      const currentSlide = slides[activeIndex];
      const nextSlide = slides[nextIndex];
      if (!currentSlide || !nextSlide) return;

      isAnimating = true;

      nextSlide.hidden = false;
      nextSlide.setAttribute('aria-hidden', 'false');

      if (direction === 'next') {
        nextSlide.classList.add('is-next');
      } else {
        nextSlide.classList.add('is-prev');
      }

      requestAnimationFrame(() => {
        currentSlide.classList.add(direction === 'next' ? 'is-leaving-left' : 'is-leaving-right');
        currentSlide.classList.remove('is-active');

        nextSlide.classList.add('is-entering');
        nextSlide.classList.remove('is-next', 'is-prev');
        nextSlide.classList.add('is-active');
      });

      nextSlide.addEventListener(
        'transitionend',
        () => {
          currentSlide.hidden = true;
          currentSlide.setAttribute('aria-hidden', 'true');
          currentSlide.classList.remove('is-leaving-left', 'is-leaving-right');

          nextSlide.classList.remove('is-entering');

          activeIndex = nextIndex;
          this.setActiveIndicator(indicators, activeIndex);
          isAnimating = false;
        },
        { once: true }
      );
    };

    previousBtn?.addEventListener('click', (event) => {
      event.preventDefault();
      const nextIndex = (activeIndex - 1 + slides.length) % slides.length;
      goTo(nextIndex, 'prev');
    });

    nextBtn?.addEventListener('click', (event) => {
      event.preventDefault();
      const nextIndex = (activeIndex + 1) % slides.length;
      goTo(nextIndex, 'next');
    });

    indicators.forEach((indicator) => {
      indicator.addEventListener('click', (event) => {
        event.preventDefault();

        const indicatorIndex = Number(indicator.dataset.uiCarouselTo ?? '-1');
        if (Number.isNaN(indicatorIndex) || indicatorIndex < 0 || indicatorIndex >= slides.length) return;

        const direction: Direction = indicatorIndex > activeIndex ? 'next' : 'prev';
        goTo(indicatorIndex, direction);
      });
    });
  }

  private setActiveIndicator(indicators: HTMLButtonElement[], activeIndex: number): void {
    indicators.forEach((indicator, index) => {
      const isActive = index === activeIndex;

      indicator.classList.toggle('is-active', isActive);
      indicator.setAttribute('aria-current', isActive ? 'true' : 'false');
    });
  }
}
