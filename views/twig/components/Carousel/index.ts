import { $ } from 'hanako-ts/dist/Framework';
import { Component } from 'hanako-ts/dist/Component';

type Direction = 'next' | 'prev';
type SwipeDirection = 'left' | 'right';

interface CarouselConfig {
  interval: number | false;
  defaultInterval: number | false;
  keyboard: boolean;
  pause: boolean;
  ride: boolean;
  touch: boolean;
  wrap: boolean;
}

interface CarouselState {
  carousel: HTMLElement;
  inner: HTMLElement;
  slides: HTMLElement[];
  indicators: HTMLButtonElement[];
  indicatorsContainer: HTMLElement | null;
  captions: HTMLElement[];
  captionsContainer: HTMLElement | null;
  prevControl: HTMLButtonElement | null;
  nextControl: HTMLButtonElement | null;
  activeIndex: number;
  isSliding: boolean;
  intervalId: number | null;
  touchStartX: number | null;
  touchDeltaX: number;
  touchTimeoutId: number | null;
  config: CarouselConfig;
}

export class Carousel extends Component {
  private static readonly EVENT_SLIDE = 'carousel:slide-complete';
  private static readonly EVENT_SLID = 'carousel:slide';
  private static readonly TOUCH_COMPAT_WAIT = 500;
  private static readonly SWIPE_THRESHOLD = 40;
  private static readonly TRANSITION_FALLBACK_MS = 800;
  private static readonly ROOT_CLASSES = ['relative'];
  private static readonly INNER_CLASSES = ['relative', 'overflow-hidden'];
  private static readonly SLIDE_BASE_CLASSES = ['w-full'];
  private static readonly SLIDE_TRANSITION_CLASSES = ['transition-transform', 'duration-700', 'ease-in-out'];
  private static readonly FADE_TRANSITION_CLASSES = ['transition-opacity', 'duration-700'];

  constructor() {
    super('Carousel', false);
  }

  public async init(): Promise<void> {
    await super.init();

    const carousels = $('[data-carousel]');
    carousels.forEach((element) => {
      if (element instanceof HTMLElement) {
        this.initCarousel(element);
      }
    });

    this.success();
  }

  private initCarousel(carousel: HTMLElement): void {
    const inner = carousel.querySelector<HTMLElement>('[data-carousel-inner]');
    if (!inner) return;

    const slides: HTMLElement[] = [];
    const indicators: HTMLButtonElement[] = [];
    const captions: HTMLElement[] = [];

    const indicatorsContainer = this.getIndicatorsContainer(carousel);
    if (indicatorsContainer) {
      indicatorsContainer.querySelectorAll<HTMLButtonElement>('[data-target]').forEach((button) => {
        indicators.push(button);
      });
    }

    const captionsContainer = this.getCaptionsContainer(carousel);
    if (captionsContainer) {
      captionsContainer.querySelectorAll<HTMLElement>('[data-carousel-caption]').forEach((caption) => {
        captions.push(caption);
      });

      if (captions.length === 0) {
        Array.from(captionsContainer.children).forEach((child) => {
          if (child instanceof HTMLElement) {
            captions.push(child);
          }
        });
      }
    }

    const prevControl = carousel.querySelector<HTMLButtonElement>('[data-carousel-control-prev], [data-slide-direction-prev]');
    const nextControl = carousel.querySelector<HTMLButtonElement>('[data-carousel-control-next], [data-slide-direction-next]');

    $(inner).find('[data-carousel-item]').forEach((element) => {
      if (element instanceof HTMLElement) {
        slides.push(element);
      }
    });

    if (slides.length === 0) return;

    const activeIndex = this.normalizeActiveIndex(slides);
    const state: CarouselState = {
      carousel,
      inner,
      slides,
      indicators,
      indicatorsContainer,
      captions,
      captionsContainer,
      prevControl,
      nextControl,
      activeIndex,
      isSliding: false,
      intervalId: null,
      touchStartX: null,
      touchDeltaX: 0,
      touchTimeoutId: null,
      config: this.getConfig(carousel)
    };

    this.decorateCarousel(state);
    this.hydrateSlides(state);
    this.setActiveIndicator(state, state.activeIndex);
    this.setActiveCaption(state, state.activeIndex);
    this.addEventListeners(state);

    if (state.config.ride) {
      this.cycle(state);
    }
  }

  private getConfig(carousel: HTMLElement): CarouselConfig {
    const defaultInterval = this.parseInterval(carousel.dataset.interval, 5000);

    return {
      interval: defaultInterval,
      defaultInterval,
      keyboard: this.parseBoolean(carousel.dataset.keyboard, true),
      pause: this.parseBoolean(carousel.dataset.hoverPause, false),
      ride: this.parseBoolean(carousel.dataset.ride, false),
      touch: this.parseBoolean(carousel.dataset.touch, true),
      wrap: this.parseBoolean(carousel.dataset.wrap, true)
    };
  }

  private parseInterval(value: string | undefined, fallback: number | false): number | false {
    if (!value || value === 'false' || value === '0') {
      return fallback;
    }

    const parsedValue = Number.parseInt(value, 10);
    return Number.isNaN(parsedValue) || parsedValue <= 0 ? fallback : parsedValue;
  }

  private parseBoolean(value: string | undefined, fallback: boolean): boolean {
    if (value === undefined) {
      return fallback;
    }

    if (value === '' || value === 'true' || value === '1') {
      return true;
    }

    if (value === 'false' || value === '0') {
      return false;
    }

    return fallback;
  }

  private normalizeActiveIndex(slides: HTMLElement[]): number {
    const stateIndex = slides.findIndex((slide) => {
      if (slide.dataset.carouselState === 'active') {
        return true;
      }

      return false;
    });

    if (stateIndex >= 0) {
      return stateIndex;
    }

    const visibleIndex = slides.findIndex((slide) => !slide.classList.contains('hidden'));
    if (visibleIndex >= 0) {
      return visibleIndex;
    }

    return 0;
  }

  private decorateCarousel(state: CarouselState): void {
    state.carousel.classList.add(...Carousel.ROOT_CLASSES);
    state.inner.classList.add(...Carousel.INNER_CLASSES);

    if (state.config.keyboard && !state.carousel.hasAttribute('tabindex')) {
      state.carousel.tabIndex = 0;
    }

    if (state.prevControl) {
      state.prevControl.setAttribute('data-slide-direction', 'prev');
    }

    if (state.nextControl) {
      state.nextControl.setAttribute('data-slide-direction', 'next');
    }

    if (state.captionsContainer) {
      state.captionsContainer.setAttribute('data-carousel-captions-ready', 'true');
    }
  }

  private hydrateSlides(state: CarouselState): void {
    const isFade = this.isFadeCarousel(state.carousel);

    state.slides.forEach((slide, index) => {
      const isActive = index === state.activeIndex;
      this.resetSlideClasses(slide);
      slide.classList.add(...Carousel.SLIDE_BASE_CLASSES);
      slide.classList.add(...(isFade ? Carousel.FADE_TRANSITION_CLASSES : Carousel.SLIDE_TRANSITION_CLASSES));

      if (isFade) {
        if (isActive) {
          slide.classList.add('relative', 'block', 'opacity-100');
        } else {
          slide.classList.add('absolute', 'inset-0', 'block', 'opacity-0');
        }
      } else if (isActive) {
        slide.classList.add('relative', 'block', 'translate-x-0');
      } else {
        slide.classList.add('hidden');
      }

      slide.dataset.carouselState = isActive ? 'active' : 'inactive';
      slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    });
  }

  private addEventListeners(state: CarouselState): void {
    $(state.carousel).on('click', (event: Event) => {
      const target = event.target as HTMLElement;
      const trigger = target.closest<HTMLElement>('[data-slide-direction]');
      if (!trigger || !state.carousel.contains(trigger)) {
        return;
      }

      event.preventDefault();

      const command = trigger.getAttribute('data-slide-direction')
        ?? (trigger.hasAttribute('data-slide-direction-prev') ? 'prev' : null)
        ?? (trigger.hasAttribute('data-slide-direction-next') ? 'next' : null);

      if (command !== 'next' && command !== 'prev') {
        return;
      }

      if (command === 'next') {
        this.next(state);
      } else {
        this.prev(state);
      }

      this.maybeEnableCycle(state);
    });

    state.indicators.forEach((indicator) => {
      indicator.addEventListener('click', (event) => {
        event.preventDefault();

        const rawIndex = indicator.getAttribute('data-target');
        if (rawIndex === null) {
          return;
        }

        const index = Number.parseInt(rawIndex, 10);
        if (Number.isNaN(index)) {
          return;
        }

        this.to(state, index);
        this.maybeEnableCycle(state);
      });
    });

    if (state.config.keyboard) {
      $(state.carousel).on('keydown', (event: KeyboardEvent) => {
        const tagName = (event.target as HTMLElement).tagName;
        if (/input|textarea/i.test(tagName)) {
          return;
        }

        let direction: SwipeDirection | null = null;
        if (event.key === 'ArrowLeft') {
          direction = 'right';
        }

        if (event.key === 'ArrowRight') {
          direction = 'left';
        }

        if (!direction) {
          return;
        }

        event.preventDefault();
        this.slide(state, this.directionToOrder(direction));
      });
    }

    if (state.config.pause === true) {
      $(state.carousel).on('mouseenter', () => this.pause(state));
      $(state.carousel).on('mouseleave', () => this.maybeEnableCycle(state));
    }

    if (state.config.touch) {
      state.slides.forEach((slide) => {
        Array.from(slide.querySelectorAll('img')).forEach((image) => {
          image.addEventListener('dragstart', (event) => event.preventDefault());
        });
      });

      $(state.carousel).on('touchstart', (event: TouchEvent) => {
        state.touchStartX = event.touches[0]?.clientX ?? null;
        state.touchDeltaX = 0;
      });

      $(state.carousel).on('touchmove', (event: TouchEvent) => {
        const currentX = event.touches[0]?.clientX;
        if (currentX === undefined || state.touchStartX === null) {
          return;
        }

        state.touchDeltaX = currentX - state.touchStartX;
      });

      $(state.carousel).on('touchend', () => {
        if (Math.abs(state.touchDeltaX) > Carousel.SWIPE_THRESHOLD) {
          const swipeDirection: SwipeDirection = state.touchDeltaX > 0 ? 'right' : 'left';
          this.slide(state, this.directionToOrder(swipeDirection));
        }

        if (state.config.pause === true) {
          this.pause(state);

          if (state.touchTimeoutId !== null) {
            window.clearTimeout(state.touchTimeoutId);
          }

          const delay = Number(state.config.interval || 0) + Carousel.TOUCH_COMPAT_WAIT;
          state.touchTimeoutId = window.setTimeout(() => {
            state.touchTimeoutId = null;
            this.maybeEnableCycle(state);
          }, delay);
        }

        state.touchStartX = null;
        state.touchDeltaX = 0;
      });
    }
  }

  private next(state: CarouselState): void {
    this.slide(state, 'next');
  }

  private prev(state: CarouselState): void {
    this.slide(state, 'prev');
  }

  private to(state: CarouselState, index: number): void {
    if (index < 0 || index >= state.slides.length) {
      return;
    }

    if (state.isSliding) {
      state.carousel.addEventListener(Carousel.EVENT_SLID, () => this.to(state, index), { once: true });
      return;
    }

    if (index === state.activeIndex) {
      return;
    }

    const direction: Direction = index > state.activeIndex ? 'next' : 'prev';
    this.slide(state, direction, index);
  }

  private slide(state: CarouselState, order: Direction, forcedIndex?: number): void {
    if (state.isSliding) {
      return;
    }

    const activeSlide = state.slides[state.activeIndex];
    if (!activeSlide) {
      return;
    }

    const nextIndex = forcedIndex ?? this.getNextIndex(state, order);
    if (nextIndex === state.activeIndex) {
      return;
    }

    const nextSlide = state.slides[nextIndex];
    if (!nextSlide) {
      return;
    }

    const slideEvent = new CustomEvent(Carousel.EVENT_SLIDE, {
      cancelable: true,
      detail: {
        relatedTarget: nextSlide,
        direction: this.orderToDirection(order),
        from: state.activeIndex,
        to: nextIndex
      }
    });

    if (!state.carousel.dispatchEvent(slideEvent)) {
      return;
    }

    const isCycling = state.intervalId !== null;
    const isFade = this.isFadeCarousel(state.carousel);
    this.pause(state);

    state.isSliding = true;
    this.setActiveIndicator(state, nextIndex);
    this.setActiveCaption(state, nextIndex);

    this.resetSlideClasses(nextSlide);
    this.resetSlideClasses(activeSlide);

    nextSlide.classList.add(...Carousel.SLIDE_BASE_CLASSES);
    activeSlide.classList.add(...Carousel.SLIDE_BASE_CLASSES);

    nextSlide.classList.add(...(isFade ? Carousel.FADE_TRANSITION_CLASSES : Carousel.SLIDE_TRANSITION_CLASSES));
    activeSlide.classList.add(...(isFade ? Carousel.FADE_TRANSITION_CLASSES : Carousel.SLIDE_TRANSITION_CLASSES));

    nextSlide.setAttribute('aria-hidden', 'false');

    if (isFade) {
      nextSlide.classList.add('absolute', 'inset-0', 'block', 'opacity-0');
      activeSlide.classList.add('relative', 'block', 'opacity-100');

      this.reflow(nextSlide);

      requestAnimationFrame(() => {
        nextSlide.classList.remove('opacity-0');
        nextSlide.classList.add('opacity-100');
        activeSlide.classList.remove('opacity-100');
        activeSlide.classList.add('opacity-0');
      });

      this.queueTransitionEnd(nextSlide, () => {
        const fromIndex = state.activeIndex;

        this.resetSlideClasses(activeSlide);
        this.resetSlideClasses(nextSlide);

        activeSlide.classList.add(...Carousel.SLIDE_BASE_CLASSES, ...Carousel.FADE_TRANSITION_CLASSES, 'absolute', 'inset-0', 'block', 'opacity-0');
        activeSlide.dataset.carouselState = 'inactive';
        activeSlide.setAttribute('aria-hidden', 'true');

        nextSlide.classList.add(...Carousel.SLIDE_BASE_CLASSES, ...Carousel.FADE_TRANSITION_CLASSES, 'relative', 'block', 'opacity-100');
        nextSlide.dataset.carouselState = 'active';

        state.activeIndex = nextIndex;
        state.isSliding = false;

        state.carousel.dispatchEvent(new CustomEvent(Carousel.EVENT_SLID, {
          detail: {
            relatedTarget: nextSlide,
            direction: this.orderToDirection(order),
            from: fromIndex,
            to: nextIndex
          }
        }));

        if (isCycling) {
          this.cycle(state);
        }
      });

      return;
    }

    nextSlide.classList.add('absolute', 'inset-0', 'block', order === 'next' ? 'translate-x-full' : '-translate-x-full');
    activeSlide.classList.add('relative', 'block', 'translate-x-0');

    this.reflow(nextSlide);

    requestAnimationFrame(() => {
      nextSlide.classList.remove(order === 'next' ? 'translate-x-full' : '-translate-x-full');
      nextSlide.classList.add('translate-x-0');

      activeSlide.classList.remove('translate-x-0');
      activeSlide.classList.add(order === 'next' ? '-translate-x-full' : 'translate-x-full');
    });

    this.queueTransitionEnd(activeSlide, () => {
      const fromIndex = state.activeIndex;

      this.resetSlideClasses(nextSlide);
      this.resetSlideClasses(activeSlide);

      nextSlide.classList.add(...Carousel.SLIDE_BASE_CLASSES, ...Carousel.SLIDE_TRANSITION_CLASSES, 'relative', 'block', 'translate-x-0');
      nextSlide.dataset.carouselState = 'active';

      activeSlide.classList.add(...Carousel.SLIDE_BASE_CLASSES, ...Carousel.SLIDE_TRANSITION_CLASSES, 'hidden');
      activeSlide.dataset.carouselState = 'inactive';
      activeSlide.setAttribute('aria-hidden', 'true');

      state.activeIndex = nextIndex;
      state.isSliding = false;

      state.carousel.dispatchEvent(new CustomEvent(Carousel.EVENT_SLID, {
        detail: {
          relatedTarget: nextSlide,
          direction: this.orderToDirection(order),
          from: fromIndex,
          to: nextIndex
        }
      }));

      if (isCycling) {
        this.cycle(state);
      }
    });
  }

  private getNextIndex(state: CarouselState, order: Direction): number {
    const delta = order === 'next' ? 1 : -1;
    const tentativeIndex = state.activeIndex + delta;

    if (tentativeIndex >= 0 && tentativeIndex < state.slides.length) {
      return tentativeIndex;
    }

    if (!state.config.wrap) {
      return state.activeIndex;
    }

    return order === 'next' ? 0 : state.slides.length - 1;
  }

  private maybeEnableCycle(state: CarouselState): void {
    if (!state.config.ride) {
      return;
    }

    if (state.isSliding) {
      state.carousel.addEventListener(Carousel.EVENT_SLID, () => this.cycle(state), { once: true });
      return;
    }

    this.cycle(state);
  }

  private cycle(state: CarouselState): void {
    this.clearInterval(state);
    this.updateInterval(state);

    if (state.config.interval === false) {
      return;
    }

    state.intervalId = window.setInterval(() => {
      if (!document.hidden && this.isVisible(state.carousel)) {
        this.next(state);
      }
    }, state.config.interval);
  }

  private pause(state: CarouselState): void {
    this.clearInterval(state);
  }

  private clearInterval(state: CarouselState): void {
    if (state.intervalId !== null) {
      window.clearInterval(state.intervalId);
      state.intervalId = null;
    }
  }

  private updateInterval(state: CarouselState): void {
    const activeSlide = state.slides[state.activeIndex];
    if (!activeSlide) {
      state.config.interval = state.config.defaultInterval;
      return;
    }

    const slideInterval = this.parseInterval(activeSlide.dataset.hwInterval, state.config.defaultInterval);
    state.config.interval = slideInterval;
  }

  private directionToOrder(direction: SwipeDirection): Direction {
    const isRtl = document.documentElement.dir === 'rtl';

    if (isRtl) {
      return direction === 'left' ? 'prev' : 'next';
    }

    return direction === 'left' ? 'next' : 'prev';
  }

  private orderToDirection(order: Direction): SwipeDirection {
    const isRtl = document.documentElement.dir === 'rtl';

    if (isRtl) {
      return order === 'prev' ? 'left' : 'right';
    }

    return order === 'prev' ? 'right' : 'left';
  }

  private isFadeCarousel(carousel: HTMLElement): boolean {
    const effect = carousel.dataset.effect;
    return effect === 'fade';
  }

  private setActiveIndicator(state: CarouselState, activeIndex: number): void {
    state.indicators.forEach((indicator, index) => {
      const isActive = index === activeIndex;

      if (isActive) {
        indicator.setAttribute('aria-current', 'true');
      } else {
        indicator.removeAttribute('aria-current');
      }
    });
  }

  private setActiveCaption(state: CarouselState, activeIndex: number): void {
    if (!state.captionsContainer || state.captions.length === 0) {
      return;
    }

    state.captions.forEach((caption, index) => {
      const isActive = index === activeIndex;
      caption.classList.toggle('hidden', !isActive);
      caption.dataset.carouselState = isActive ? 'active' : 'inactive';
    });
  }

  private queueTransitionEnd(element: HTMLElement, callback: () => void): void {
    let done = false;

    const complete = () => {
      if (done) {
        return;
      }

      done = true;
      callback();
    };

    const handleTransitionEnd = (event: Event) => {
      if (event.target !== element) {
        return;
      }

      element.removeEventListener('transitionend', handleTransitionEnd);
      complete();
    };

    element.addEventListener('transitionend', handleTransitionEnd);

    window.setTimeout(() => {
      element.removeEventListener('transitionend', handleTransitionEnd);
      complete();
    }, Carousel.TRANSITION_FALLBACK_MS);
  }

  private reflow(element: HTMLElement): void {
    void element.offsetHeight;
  }

  private isVisible(element: HTMLElement): boolean {
    if (!element.isConnected) {
      return false;
    }

    const style = window.getComputedStyle(element);
    return style.display !== 'none' && style.visibility !== 'hidden' && element.offsetParent !== null;
  }

  private resetSlideClasses(slide: HTMLElement): void {
    slide.classList.remove(
      'relative',
      'absolute',
      'inset-0',
      'block',
      'hidden',
      'translate-x-0',
      'translate-x-full',
      '-translate-x-full',
      'opacity-0',
      'opacity-100',
      'transition-transform',
      'transition-opacity',
      'duration-700',
      'ease-in-out'
    );
  }

  private getIndicatorsContainer(carousel: HTMLElement): HTMLElement | null {
    const innerContainer = carousel.querySelector<HTMLElement>('[data-carousel-indicators]');
    if (innerContainer) {
      return innerContainer;
    }

    const siblingContainer = carousel.nextElementSibling;
    if (siblingContainer instanceof HTMLElement && siblingContainer.hasAttribute('data-carousel-indicators')) {
      return siblingContainer;
    }

    return null;
  }

  private getCaptionsContainer(carousel: HTMLElement): HTMLElement | null {
    const innerContainer = carousel.querySelector<HTMLElement>('[data-carousel-captions]');
    if (innerContainer) {
      return innerContainer;
    }

    let sibling = carousel.nextElementSibling;
    while (sibling) {
      if (sibling instanceof HTMLElement && sibling.hasAttribute('data-carousel')) {
        return null;
      }

      if (sibling instanceof HTMLElement && sibling.hasAttribute('data-carousel-captions')) {
        return sibling;
      }

      sibling = sibling.nextElementSibling;
    }

    return null;
  }
}
