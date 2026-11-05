function HotSearch(options) {

  // Consts
  const wrapperElement = document.querySelector(options.wrapper || '#hotsearch');
  const formElement = document.querySelector(options.form || '#hotsearch');
  const inputElement = formElement.querySelector('input[name=\"hotsearch\"]');
  const productsCount = options.productCount || 15;
  const categoriesCount = options.categoriesCount || 10;
  const mobileBreakpoint = options.breakpoint || 992;
  const minLength = options.minLength || 3;
  const showSku = options.sku || false;
  const showModel = options.model || false;
  const showStock = options.stock || false;
  const showPrice = options.price || false;
  const inputTimeout = 300;

  // Variables
  let mobile = false;
  let onChangeTimeout = null;
  let positionTop = 0;
  let height = 0;
  let data = null;

  // Listener
  formElement.addEventListener('submit', inputHandler);
  inputElement.addEventListener('input', inputHandler);
  window.addEventListener('resize', inputHandler);

  // С прерыванием быстрых нажатий
  function inputHandler(event) {
    event.preventDefault();
    clearTimeout(onChangeTimeout);
    onChangeTimeout = setTimeout(() => {
      init();
    }, inputTimeout);
  }

  function init() {

    // Очищаем предыдущее окно
    clearLive();

    // Если значение маленькое, то молчим
    const value = inputElement.value;
    if (value.length < minLength) return;

    // Определяются при инициализации окна
    // positionTop = getPosition(wrapperElement) + wrapperElement.offsetHeight + 10 + 'px';
    const coords = wrapperElement.getBoundingClientRect();
    positionTop = coords.bottom + 10 + 'px';
    mobile = getMobile();
    height = `calc(100vh - ${positionTop} - 1rem)`;
    if (mobile) {
      height = `calc(100vh - ${positionTop} - 8rem)`;
    }

    // Получаем данные
    getSearchResult(value)
      .then((result) => {
        data = result;

        // Создаем окно
        const live = createLive(data);
        document.body.appendChild(live);

        // Блокируем экран
        blockedWindow();
      })
      .catch(error => console.error(error));

  }

  function getSearchResult(value) {

    return fetch(`/index.php?route=extension/module/hotsearch/get&value=${value}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
      .then(response => response.json());

  }

  function categoryHandler({ target }) {

    const category_id = target.dataset.categoryId;
    const cellProducts = document.querySelector('#live-products');

    if (category_id && cellProducts) {
      cellProducts.innerHTML = '';
      const products = createProducts(data, category_id);
      if (products) {
        cellProducts.appendChild(products);
        document.querySelectorAll('.live-categories li a').forEach(li => li.classList.remove('active'))
        target.classList.add('active');
      }
    }

  }

  function blockedHandler({ target }) {

    const liveResult = document.querySelector('.live-result');

    if (liveResult && liveResult.contains(target)) return;

    clearLive();
    document.querySelector('.live_blocked').removeEventListener('click', blockedHandler);
    document.querySelector('.live_blocked').classList.remove('live_blocked');

  }

  function blockedWindow() {
    document.body.classList.add('live_blocked');
    document.querySelector('.live_blocked').addEventListener('click', blockedHandler);
  }

  function createLive(data) {

    const live = createElement('div', 'live');
    live.style.top = positionTop;

    const wrapper = createElement('div', 'g-wrapper');

    if (data.error) {

      const empty = createEmpty(data.error);
      wrapper.appendChild(empty);

    } else {

      const result = createResult(data);
      result.style.maxHeight = height;
      wrapper.appendChild(result);

    }

    live.appendChild(wrapper);
    return live;

  }

  function createEmpty(error) {

    const result = createElement('div', 'live-result live-result_empty', error);
    return result;

  }

  function createResult(data, category_id = 0) {

    clearResult();

    const result = createElement('div', 'live-result');

    const row = createElement('div', 'g-row');

    // Категории
    const cellCategories = createElement('div', 'g-cell live-cell_categories');

    const categories = createCategoriesList(data.categories);
    cellCategories.appendChild(categories);

    // Товары
    const cellProducts = createElement('div', 'g-cell live-cell_products');
    cellProducts.setAttribute('id', 'live-products');

    const products = createProducts(data, category_id);
    cellProducts.appendChild(products);

    // Ссылка на стандартный поиск
    const all = createAll(data.all);

    row.appendChild(cellCategories);
    row.appendChild(cellProducts);

    result.appendChild(row);
    result.appendChild(all);

    return result;

  }

  function createCategoriesList(dataCategories) {

    let limit = categoriesCount;
    if (mobile) {
      limit = Math.floor(categoriesCount / 3);
    }

    const list = createElement("ul", 'live-categories');
    dataCategories.forEach((category, i) => {

      if (i > limit) return;

      const item = document.createElement("li");
      const link = document.createElement("a");

      if (i === 0) link.classList.add('active');

      link.dataset.count = category.count;
      link.dataset.categoryId = category.category_id;
      link.innerText = category.name;

      link.addEventListener('click', categoryHandler);

      item.appendChild(link);
      list.appendChild(item);

    });

    return list;
  }

  function createProducts({ products: dataProducts }, category_id) {


    category_id = parseInt(category_id);
    let products;

    if (category_id !== 0) {
      products = dataProducts.filter(({ categories }) => {
        const result = categories.findIndex((cat_id) => cat_id === category_id);
        if (result !== -1) return true;
        return false;
      });
    } else {

      products = dataProducts.filter((product, i) => i < productsCount);
    }

    const column = createProductList(products);
    return column;

  }

  function createProductList(dataProducts) {

    const category = createElement('div', 'live-category');

    const rowProducts = createElement('div', 'g-row');

    dataProducts.forEach((dataProduct, i) => {

      if (i >= productsCount) return;

      const cell = createElement('div', 'g-cell live-cell_product');

      const product = createProduct(dataProduct);
      cell.appendChild(product);
      rowProducts.appendChild(cell);

    });

    category.appendChild(rowProducts)

    return category;

  }

  function createProduct(dataProduct) {

    const fragment = createLink(dataProduct.href, 'live-product');

    // Изображение
    const cellImage = createElement("div", 'live-product__image');

    const image = createImage(dataProduct.thumb, null, dataProduct.name);
    cellImage.appendChild(image);

    // Информация
    const cellInformation = createElement("div", 'live-product__information');

    const name = createElement("div", 'live-product__name', dataProduct.name);
    cellInformation.appendChild(name);

    if (showStock) {
      const stock = createElement("div", 'live-stock', dataProduct.stock);
      if (dataProduct.stock_class) stock.classList.add('live-stock_' + dataProduct.stock_class);
      cellInformation.appendChild(stock);

    }

    if (showModel && dataProduct.model) {
      const model = createElement("div", 'live-model', dataProduct.model);
      cellInformation.appendChild(model);
    }

    if (showSku && dataProduct.sku) {
      const sku = createElement("div", 'live-sku', dataProduct.sku);
      cellInformation.appendChild(sku);
    }

    if (showPrice) {
      const price = createPrice(dataProduct.price, dataProduct.special);
      cellInformation.appendChild(price);
    }

    fragment.appendChild(cellImage);
    fragment.appendChild(cellInformation);

    return fragment;

  }

  function createPrice(dataPrice, dataSpecial = false) {

    let price;

    if (dataSpecial) {
      price = createElement("div", 'live-price');
      const special = createElement("span", 'live-price__special', dataSpecial);
      price.appendChild(special);
      const old = createElement("span", 'live-price__old', dataPrice);
      price.appendChild(old);
    } else {
      price = createElement("div", 'live-price', dataPrice);
    }

    return price;
  }

  function createAll(dataAll) {
    const div = createElement('div', 'live-all');
    const link = createLink(dataAll.href, null, dataAll.title);
    div.appendChild(link);
    return div;
  }

  function clearResult() {
    const prevs = document.querySelectorAll('.live-result');
    prevs.forEach(prev => prev.remove());
  }

  function clearLive() {
    const prevs = document.querySelectorAll('.live');
    prevs.forEach(prev => prev.remove());
  }

  function getMobile() {
    if (window.innerWidth < mobileBreakpoint) {
      return true;
    }
    return false;
  }

  // Helper Create HTML element
  function createElement(type, classNames = '', text = '') {
    let element = document.createElement(type);
    if (classNames) element = addClasses(element, classNames);
    if (text) element.innerText = text;
    return element;
  }

  function createLink(href = '', classNames = '', text = '') {
    let element = document.createElement("a");
    if (classNames) element = addClasses(element, classNames);
    if (href) element.setAttribute('href', href.replace(/&amp;/g, '&'));
    if (text) element.innerText = text;
    return element;
  }

  function createImage(src = '', classNames = '', text = '') {
    let element = document.createElement("img");
    if (classNames) element = addClasses(element, classNames);
    if (src) element.setAttribute('src', src);
    if (text) element.setAttribute('alt', text);
    return element;
  }

  function addClasses(element, classes) {
    classes.split(' ').forEach((name) => {
      element.classList.add(name);
    });
    return element;
  }

};