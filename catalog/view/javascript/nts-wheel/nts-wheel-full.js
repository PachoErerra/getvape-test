const prizeSets = {
  basic: [
    {
      probability: 21,
      text: 'Скинь другу \n колесо та отримай \n 10% знижку ',
      textConfig: {
        x: 170, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 15,
        font: '900 16px Gilroy', // опционально
        lineHeight: 15 // опционально
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/invite-friend.png',
      imgConfig: {
        x: 185,
        y: -25,
        rotation: 10,
        size: {w: 67, h: 36}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.InviteFriend('friend'))
    },
    {
      probability: 6,
      text: '',
      img2x: 'catalog/view/javascript/nts-wheel/media/1_1_3_2x.png',
      imgConfig: {
        x: 170,
        y: -5,
        rotation: -35,
        size: {w: 123, h: 109}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Liquids('1_1_3'))

    },
    {
      probability: 13,
      text: '',
      img2x: 'catalog/view/javascript/nts-wheel/media/cartridge-2x-gift.png',
      imgConfig: {
        x: 160,
        y: 2,
        rotation: -90,
        size: {w: 84, h: 95}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.CartridgeGift('free'))
    },
    {
      probability: 5,
      text: '100 бонусів на \n рахунок',
      textConfig: {
        x: 160, // px от центра
        y: -18,   // смещение вверх/вниз от центра
        rotation: 170,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/bonuses-blue-2x.png',
      imgConfig: {
        x: 165,
        y: 25,
        rotation: 170,
        size: {w: 40, h: 40}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Bonuses(100))
    },
    {
      probability: 9,
      text: '1 = 2',
      textConfig: {
        x: 140, // px от центра
        y: 0,   // смещение вверх/вниз от центра
        rotation: 180,
        font: '900 32px Gilroy', // опционально
        lineHeight: 15 // опционально
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/1_2.png',
      imgConfig: {
        x: 200,
        y: 10,
        rotation: -140,
        size: {w: 101, h: 76}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Liquids('1_2'))
    },
    {
      probability: 8,
      text: 'Секретний \n подарунок',
      textConfig: {
        x: 175, // px от центра
        y: -20,   // смещение вверх/вниз от центра
        rotation: 160,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/gift-2x.png',
      imgConfig: {
        x: 185,
        y: 25,
        rotation: 160,
        size: {w: 40, h: 45}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.SecretGift('socks'))
    },
    {
      probability: 19,
      text: 'Безкоштовна \n доставка',
      textConfig: {
        x: 160, // px от центра
        y: -20,   // смещение вверх/вниз от центра
        rotation: 165,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/free-delivery.png',
      imgConfig: {
        x: 180,
        y: 20,
        rotation: 165,
        size: {w: 73, h: 51}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.FreeDelivery('freeDelivery'))
    },
    {
      probability: 0, // по умолчанию 0, чтобы по вероятности он не выпадал
      text: 'Крутити ще',
      textConfig: {
        x: 165,
        y: -12,
        rotation: 170,
        // font: '900 20px Gilroy',
        // lineHeight: 15/
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/retry-2x.png',
      imgConfig: {
        x: 170,
        y: 20,
        rotation: 160,
        size: {w: 31, h: 36}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Repeat())
    },
    {
      probability: 11,
      text: 'Знижка 15%',
      textConfig: {
        x: 155, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 15,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/percent-2x.png',
      imgConfig: {
        x: 165,
        y: -20,
        rotation: 10,
        size: {w: 40, h: 41}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.PromoPercentage(15))
    },
    {
      probability: 8,
      text: 'Знижка 20%',
      textConfig: {
        x: 155, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 12,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/percent-red-2x.png',
      imgConfig: {
        x: 165,
        y: -22,
        rotation: 30,
        size: {w: 66, h: 50}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.PromoPercentage(20))
    },
  ],
  newCustomer: [
    {
      probability: 0,
      text: 'Скинь другу \n колесо та отримай \n 10% знижку ',
      textConfig: {
        x: 170, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 15,
        font: '900 16px Gilroy', // опционально
        lineHeight: 15 // опционально
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/invite-friend.png',
      imgConfig: {
        x: 185,
        y: -25,
        rotation: 10,
        size: {w: 67, h: 36}
      },
    },
    {
      probability: 0,
      text: '',
      img2x: 'catalog/view/javascript/nts-wheel/media/1_1_3_2x.png',
      imgConfig: {
        x: 170,
        y: -5,
        rotation: -35,
        size: {w: 123, h: 109}
      },
    },
    {
      probability: 0,
      text: '',
      img2x: 'catalog/view/javascript/nts-wheel/media/cartridge-2x-gift.png',
      imgConfig: {
        x: 160,
        y: 2,
        rotation: -90,
        size: {w: 84, h: 95}
      },
    },
    {
      probability: 0,
      text: '100 бонусів на \n рахунок',
      textConfig: {
        x: 160, // px от центра
        y: -18,   // смещение вверх/вниз от центра
        rotation: 170,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/bonuses-blue-2x.png',
      imgConfig: {
        x: 165,
        y: 25,
        rotation: 170,
        size: {w: 40, h: 40}
      },
    },
    {
      probability: 0,
      text: '1 = 2',
      textConfig: {
        x: 140, // px от центра
        y: 0,   // смещение вверх/вниз от центра
        rotation: 180,
        font: '900 32px Gilroy', // опционально
        lineHeight: 15 // опционально
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/1_2.png',
      imgConfig: {
        x: 200,
        y: 10,
        rotation: -140,
        size: {w: 101, h: 76}
      },
    },
    {
      probability: 0,
      text: 'Секретний \n подарунок',
      textConfig: {
        x: 175, // px от центра
        y: -20,   // смещение вверх/вниз от центра
        rotation: 160,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/gift-2x.png',
      imgConfig: {
        x: 185,
        y: 25,
        rotation: 160,
        size: {w: 40, h: 45}
      },
    },
    {
      probability: 0,
      text: 'Безкоштовна \n доставка',
      textConfig: {
        x: 160, // px от центра
        y: -20,   // смещение вверх/вниз от центра
        rotation: 165,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/free-delivery.png',
      imgConfig: {
        x: 180,
        y: 20,
        rotation: 165,
        size: {w: 73, h: 51}
      },
    },
    {
      probability: 100, // по умолчанию 0, чтобы по вероятности он не выпадал
      text: '',//Шкарпетка
      textConfig: {
        x: 165,
        y: -12,
        rotation: 170,
        // font: '900 20px Gilroy',
        // lineHeight: 15/
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/socks.png',
      imgConfig: {
        x: 170,
        y: 8,
        rotation: 100,
        size: {w: 80, h: 96}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.OneSock())

    },
    {
      probability: 0,
      text: 'Знижка 15%',
      textConfig: {
        x: 155, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 15,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/percent-2x.png',
      imgConfig: {
        x: 165,
        y: -20,
        rotation: 10,
        size: {w: 40, h: 41}
      },
    },
    {
      probability: 0,
      text: 'Знижка 20%',
      textConfig: {
        x: 155, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 12,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/percent-red-2x.png',
      imgConfig: {
        x: 165,
        y: -22,
        rotation: 30,
        size: {w: 66, h: 50}
      },
    },
  ],
  regular: [ // https://prnt.sc/QbVqWlJAFnYj
    // {
    //   probability: 21,
    //   text: '',
    //   textConfig: {
    //     x: 160, // px от центра
    //     y: -20,   // смещение вверх/вниз от центра
    //     rotation: 12,
    //   },
    //   img2x: 'catalog/view/javascript/nts-wheel/media/vapors-pen.png',
    //   imgConfig: {
    //     x: 160,
    //     y: 0,
    //     rotation: -100,
    //     size: {w: 100, h: 113}
    //   },
    //   onWin: () => renderPrize(PRIZE_HANDLERS.Merch('pen'))
    // },
    {
      probability: 21,
      text: '',
      textConfig: {
        x: 160, // px от центра
        y: -20,   // смещение вверх/вниз от центра
        rotation: 12,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/vapors-bracelet-pink.png',
      imgConfig: {
        x: 160,
        y: 0,
        rotation: 2,
        size: { w: 84, h: 42 }
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Bracelet('bracelet-pink'))
    },
    {
      probability: 19,
      text: 'Безкоштовна \n доставка',
      textConfig: {
        x: 170, // px от центра
        y: 22,   // смещение вверх/вниз от центра
        rotation: 18,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/free-delivery.png',
      imgConfig: {
        x: 180,
        y: -18,
        rotation: 8,
        size: {w: 73, h: 51}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.FreeDelivery('freeDelivery'))
    },
    {
      probability: 13,
      text: '',
      img2x: 'catalog/view/javascript/nts-wheel/media/oxva-wash-bag.png',
      imgConfig: {
        x: 170,
        y: 2,
        rotation: -180,
        size: {w: 108, h: 56}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Merch('cosmetic'))
    },
    {
      probability: 5,
      text: '',
      textConfig: {
        x: 160, // px от центра
        y: -18,   // смещение вверх/вниз от центра
        rotation: 170,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/oxva-bandana.png',
      imgConfig: {
        x: 175,
        y: -5,
        rotation: 110,
        size: {w: 74, h: 85}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Merch('bandana'))
    },

    {
      probability: 5,
      text: '+40 грн',
      textConfig: {
        x: 160, // px от центра
        y: -10,   // смещение вверх/вниз от центра
        rotation: 170,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/gift-2x.png',
      imgConfig: {
        x: 160,
        y: 20,
        rotation: -185,
        size: {w: 40, h: 40}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Bonuses(40))
    },
    {
      probability: 0,
      text: '',
      textConfig: {
        x: 160, // px от центра
        y: 0,   // смещение вверх/вниз от центра
        rotation: 160,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/vapors-socks.png',
      imgConfig: {
        x: 175,
        y: 5,
        rotation: 160,
        size: {w: 100, h: 93}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.SecretGift('socks'))
    },

    {
      probability: 8,
      text: '',
      textConfig: {
        x: 155, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 165,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/oxva-keychain.png',
      imgConfig: {
        x: 170,
        y: -5,
        rotation: 90,
        size: {w: 100, h: 100}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Merch('keychain'))
    },

    {
      probability: 0, // по умолчанию 0, чтобы по вероятности он не выпадал
      text: 'Крутити ще',
      textConfig: {
        x: 165,
        y: -12,
        rotation: 170,
        // font: '900 20px Gilroy',
        // lineHeight: 15/
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/retry-2x.png',
      imgConfig: {
        x: 170,
        y: 20,
        rotation: 160,
        size: {w: 31, h: 36}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Repeat())
    },
    {
      probability: 11,
      text: 'x2 кешбек',
      textConfig: {
        x: 155, // px от центра
        y: 15,   // смещение вверх/вниз от центра
        rotation: 15,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/percent-2x.png',
      imgConfig: {
        x: 165,
        y: -20,
        rotation: 10,
        size: {w: 40, h: 41}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.DoubleCashback())
    },
    {
      probability: 19,
      text: '',
      textConfig: {
        x: 160, // px от центра
        y: -20,   // смещение вверх/вниз от центра
        rotation: 12,
      },
      img2x: 'catalog/view/javascript/nts-wheel/media/vapors-lanyard.png',
      imgConfig: {
        x: 160,
        y: 0,
        rotation: 100,
        size: {w: 90, h: 90}
      },
      onWin: () => renderPrize(PRIZE_HANDLERS.Merch('lanyard'))
    },
  ],
}


const wheelEl = document.getElementById('wheel-modal');
const wheelStyle = wheelEl?.dataset?.style || 'basic';
const wheelPrizeType = wheelEl?.dataset?.type || 'basic';


const wheelStyles = {
  basic: {
    gradients: [
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, 0, size, 0);
        grad.addColorStop(0.001, '#63DCFC');
        grad.addColorStop(1, '#B77991');
        return grad;
      },
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, size, size, 0);
        grad.addColorStop(0.5081, 'rgba(231, 32, 108, 0.80)');
        grad.addColorStop(0.5517, 'rgba(231, 32, 107, 0.88)');
        grad.addColorStop(0.6120, 'rgba(231, 32, 106, 0.95)');
        grad.addColorStop(0.6758, 'rgba(231, 32, 106, 0.99)');
        grad.addColorStop(0.7629, '#E7216A');
        return grad;
      },
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, 0, size, size);
        grad.addColorStop(0.2826, '#000000');
        grad.addColorStop(0.8462, '#560B24');
        return grad;
      },
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, 1, size, 0);
        grad.addColorStop(1, '#B77991');
        grad.addColorStop(1, '#789abb');
        return grad;
      },
    ],
    sectorGradients: [1, 0, 1, 0, 1, 2, 0, 3, 1, 0]
  },

  halloween: {
    gradients: [
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, 0, size, size);
        grad.addColorStop(0, '#EA5A00');
        grad.addColorStop(1, '#EBAC00');
        return grad;
      },
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, size, size, 0);
        grad.addColorStop(0, '#DE5327');
        grad.addColorStop(1, '#A00606');
        return grad;
      },
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, 0, size, size);
        grad.addColorStop(0.2826, '#000000');
        grad.addColorStop(0.8462, '#560B24');
        return grad;
      },
      function (ctx, size) {
        let grad = ctx.createLinearGradient(0, 0, size, size);
        grad.addColorStop(0, '#EA5A00');
        grad.addColorStop(1, '#EBAC00');
        return grad;
      }
    ],
    sectorGradients: [1, 0, 1, 0, 1, 2, 0, 3, 1, 0]
  },
};


// Для каждого сектора выбираем градиент по индексу (цикл)
// const sectorGradients = [1, 0, 1, 0, 1, 2, 0, 3, 1, 0];
const currentStyle = wheelStyles[wheelStyle] || wheelStyles.basic;

// Чтобы не выходить за границы массива, проверяем индексы
const gradients = currentStyle.gradients;
const sectorGradients = currentStyle.sectorGradients;

// Перестраховка: если сектор выйдет за диапазон, подставляем 0-й градиент
function getGradient(index) {
  const gIndex = sectorGradients[index] ?? 0;
  return gradients[gIndex % gradients.length];
}


// Функция рисования колеса с изображениями
function drawWheel(prizes, initialRotation = 0) {
  const canvas = document.createElement('canvas');
  const size = 520;
  const dpr = window.devicePixelRatio || 1;
  const initialRotationRad = (initialRotation * Math.PI) / 180;


  canvas.width = size * dpr;
  canvas.height = size * dpr;
  canvas.style.width = size + 'px';
  canvas.style.height = size + 'px';

  const ctx = canvas.getContext('2d');
  ctx.setTransform(1, 0, 0, 1, 0, 0);
  ctx.scale(dpr, dpr);

  const sectorCount = prizes.length;
  const sectorAngle = 2 * Math.PI / sectorCount;

  ctx.clearRect(0, 0, size, size);

  // Для загрузки картинок создадим массив промисов
  const imgPromises = prizes.map(prize => {
    return new Promise((resolve) => {
      if (prize.img2x) {
        const img = new Image();
        img.src = prize.img2x;
        img.onload = () => resolve(img);
        img.onerror = () => resolve(null); // если не загрузится
      } else {
        resolve(null);
      }
    });
  });

  return Promise.all(imgPromises).then(images => {
    for (let i = 0; i < sectorCount; i++) {
      const startAngle = i * sectorAngle + initialRotationRad; //
      const endAngle = startAngle + sectorAngle;

      // Рисуем сектор с градиентом
      ctx.fillStyle = getGradient(i)(ctx, size);
      ctx.beginPath();
      ctx.moveTo(size / 2, size / 2);
      ctx.arc(size / 2, size / 2, size / 2 - 5, startAngle, endAngle);
      ctx.closePath();
      ctx.fill();

      ctx.lineWidth = 5;
      ctx.strokeStyle = wheelStyle === 'basic' ? '#fff' : '#FFD291';
      ctx.stroke();


      // Настройки текста
      const tcfg = prizes[i].textConfig || {};


      // Рисуем текст
      ctx.save();
      ctx.translate(size / 2, size / 2);             // центр круга
      ctx.rotate(startAngle + sectorAngle / 2);     // поворот в сектор

      const tx = tcfg.x !== undefined ? tcfg.x : 150;
      const ty = tcfg.y || 0;

      ctx.translate(tx, ty);                         // смещаемся к точке отрисовки текста

      const rotationRadians = ((tcfg.rotation || 0) * Math.PI) / 180;
      ctx.rotate(rotationRadians);                   // поворот текста вокруг своей оси (локальный)

      // рисуем текст в точке (0,0)
      ctx.fillStyle = tcfg.color || "#fff";
      ctx.font = tcfg.font || "900 20px Gilroy, sans-serif";
      ctx.textAlign = tcfg.align || "center";
      ctx.textBaseline = "middle";

      const lines = prizes[i].text.split('\n');
      const lineHeight = tcfg.lineHeight || 20;

      lines.forEach((line, idx) => {
        ctx.fillText(line.trim(), 0, (idx - (lines.length - 1) / 2) * lineHeight);
      });

      ctx.restore();


      // Рисуем картинку с поворотом по центру сектора
      const img = images[i];
      if (img) {
        const cfg = prizes[i].imgConfig || {};
        const sizeImg = cfg.size || {w: 100, h: 100};
        const x = cfg.x ?? 0; // смещение от центра сектора по оси X
        const y = cfg.y ?? 0; // смещение от центра сектора по оси Y
        const angle = (cfg.rotation || 0) * Math.PI / 180;

        ctx.save();
        ctx.translate(size / 2, size / 2);               // в центр круга
        ctx.rotate(startAngle + sectorAngle / 2);       // поворот в сектор
        ctx.translate(x, y);                             // смещение внутри сектора
        ctx.rotate(angle);                               // поворот картинки
        ctx.drawImage(img, -sizeImg.w / 2, -sizeImg.h / 2, sizeImg.w, sizeImg.h);
        ctx.restore();
      }
    }
    return canvas;
  });
}

// Инициализация
const wheelElem = document.getElementById('wheel');
wheelElem.innerHTML = '';

const startAngle = -18; // например, стартовый поворот колеса 45 градусов

const prizes = prizeSets[wheelPrizeType] || prizeSets.basic;


drawWheel(prizes, startAngle).then(wheelCanvas => {
  wheelElem.appendChild(wheelCanvas);
});


function getRandomSectorIndexByProbability(prizes) {
  const total = prizes.reduce((sum, seg) => sum + (seg.probability || 0), 0);
  const rnd = Math.random() * total;

  let cumulative = 0;
  for (let i = 0; i < prizes.length; i++) {
    cumulative += prizes[i].probability;
    if (rnd < cumulative) {
      return i;
    }
  }

  return prizes.length - 1;
}


let spinCount = 0; // Счетчик количества вращений
let maxSpins = 2; // Максимальное количество вращений

// Крутилка
function spin() {
  if (spinCount >= maxSpins) {
    alert("Ви вже крутили колесо сьогодні.\nПриходьте завтра!");
    return;
  }

  document.getElementById("fortune__prize").style.display = 'none';

  const sectorCount = prizes.length;
  const sectorAngle = 360 / sectorCount;

  let sectorIndex;


  if (spinCount === 0) {
    if (wheelPrizeType == 'newCustomer') sectorIndex = prizes.findIndex(p => p.probability === 100);    // первый спин — всегда сектор "Шкарпетка"
    else sectorIndex = prizes.findIndex(p => p.text.includes('Крутити'));    // первый спин — всегда сектор "Крутити ще"
  } else {
    // второй и дальше — по вероятности
    sectorIndex = getRandomSectorIndexByProbability(prizes);
  }

  const spins = 10;
  const centerAngle = sectorIndex * sectorAngle + sectorAngle / 2;

  const startAngle = 18; // смещение
  const stopAngle = 360 * spins + (270 - centerAngle) + startAngle;

  wheelElem.style.transition = 'transform 12s cubic-bezier(0.1, 0.9, 0.2, 1)';
  wheelElem.style.transform = `rotate(${stopAngle}deg)`;

  spinCount++;
  document.getElementById("fortune__info-content").style.display = 'none';
  document.getElementById("fortune__info-loader").classList.add('is-show');

  setTimeout(() => {
    const prize = prizes[sectorIndex];
    if (typeof prize.onWin === 'function') {
      prize.onWin();
    }
    document.getElementById("fortune__prize").style.display = 'block';
    document.getElementById("fortune__info-loader").classList.remove('is-show');
  }, 12000);

  setTimeout(() => {
    wheelElem.style.transition = 'none';
    const normalizedAngle = stopAngle % 360;
    wheelElem.style.transform = `rotate(${normalizedAngle}deg)`;
  }, 12500);
}

// document.getElementById('spinBtn').addEventListener('click', spin);


/**
 * PRIZE_HANDLERS — набор обработчиков выигрышей
 *
 * Каждый метод возвращает объект:
 * {
 *   title: string,        // HTML заголовка приза
 *   text: string,         // HTML описания приза
 *   prizeKey: string,     // значение, отправляемое в ajaxSetPrize
 *   showPromoCode: bool   // показывать ли блок с промокодом
 * }
 *
 * Методы:
 * - PromoPercentage(percentage) - скидка % через промокод
 * - OneSock() - подарок шкарпетка + таймер
 * - FreeDelivery(code) - бесплатная доставка
 * - Repeat() - крутить еще
 * - Bonuses(amount) - бонусы в грн
 * - SecretGift(key) - секретный подарок
 * - Bracelet(color) - браслет Vapors
 * - CartridgeGift(key) - бесплатный картридж
 * - InviteFriend(key) - промокод за приглашение друга
 * - Liquids(type) - акции 1=2 и 1+1=3
 * - Merch(type) - мерч  (косметичка / бандана / брелок / ручка / ланьярд)
 * - DoubleCashback() - двойной кешбек
 */

const LIMIT_TEXT = `
  <p>Виграш діє 3 дні з моменту отримання.</p>
  <p>Через 15 днів ви зможете знову випробувати удачу.</p>
  <p>Переглянути промокод можна у розділі <a href="/promocodes/" style="text-decoration: underline;color: var(--blue-color-hover);">«Мої промокоди»</a>.</p>
`;

const PRIZE_HANDLERS = {
  PromoPercentage(percentage) {
    return {
      title: `<b>Промокод на  знижку</b> <span>${percentage}%!</span>`,
      text: ` <p>Використовуйте промокод при покупці та отримайте ${percentage}% знижки. Промокод автоматично застосується у вашому кошику.</p>
              ${LIMIT_TEXT}`,

      prizeKey: percentage + 'p',
      showPromoCode: false
    }
  },
  OneSock() {
    const deadline = Date.now() + 24 * 60 * 60 * 1000; // 24 часа

    return {
      title: `<span>Ура</span> <br> <b>шкарпетка!</b>`,

      text: `
        <p>Ви отримали шкарпетку до вашого замовлення, щоб отримати:</p>

        <ul class="fortune__info-list">
          <li><b>01</b> — оберіть товар на сайті</li>
          <li><b>02</b> — додайте його в кошик</li>
          <li><b>03</b> — оформіть замовлення</li>
          <li><b>04</b> — отримуйте безкоштовну шкарпетку</li>
        </ul>

        <a class="fortune__info-action-btn button-light-blue" href="/specials/">Скористатись акцією</a>

        <div class="countdown" style="margin-top: 24px;">
          <div class="countdown__title d-flex align-items-center">
            <svg class="sprite-icon">
              <use href="catalog/view/theme/default/image/sprites/sprites.svg?v=#icon-alert-cart"></use>
            </svg>
            Ваш виграш буде анульований через:
          </div>

          <div class="countdown__items d-flex" data-deadline="${deadline}">
            <div class="countdown__item">
              <span class="countdown__item-value hours"></span>
              <span class="countdown__item-text">Год.</span>
            </div>

            <div class="countdown__item">
              <span class="countdown__item-value minutes"></span>
              <span class="countdown__item-text">Хв.</span>
            </div>

            <div class="countdown__item">
              <span class="countdown__item-value seconds"></span>
              <span class="countdown__item-text">Сек.</span>
            </div>
          </div>
        </div>
      `,

      prizeKey: 'OneSock',

      showPromoCode: false
    };
  },
  FreeDelivery(win) {
    return {
      title: '<span>Безкоштовна доставка</span> <br> <b>для замовлення!</b>',
      text: `
            <p>Оформіть покупку на будь-яку суму — і ми доставимо її безкоштовно.</p>
            ${LIMIT_TEXT}
            `,

      prizeKey: win,
      showPromoCode: true

    }
  },

  Repeat() {// Крутити ще
    return {
      title: `<span>Крутити</span> <br> <b>ще раз!</b>`,
      text: ` <p>Отримай ще один шанс виграти подарунок або знижку.</p>
             <p>Натискай кнопку та крути колесо знову — твій приз уже чекає!</p>
            <button type="button" class="fortune__info-action-btn button-light-blue" onClick="spin();">Крутити ще</button>`,
      showPromoCode: false
    }
  },

  Bonuses(amount) {
    return {
      title: `<span>+${amount} грн</span> <b>бонусів до покупки!</b>`,
      text: `<p>На ваш бонусний рахунок нараховано <b>${amount} грн</b>!</p>
             <a class="fortune__info-action-btn button-light-blue" href="/specials/">Обрати товари</a>`,

      prizeKey: amount + 'b',
      showPromoCode: false
    }
  },

  SecretGift(socks) {
    return {
      title: `<span>Секретний</span> <b>подарунок</b>`,
      text: `<p>Ми самі додамо до вашого замовлення невеликий приємний сюрприз 🎁.</p>
            <p>Нічого робити не потрібно — просто оформіть покупку на будь-яку суму.</p>
            ${LIMIT_TEXT}
            <a class="fortune__info-action-btn button-light-blue" href="/specials/">Обрати товари</a>   `,
      prizeKey: socks,
      showPromoCode: false
    }
  },

  Bracelet(color) {
    return {
      title: `<b>Фірмовий браслет</b> <span>Vapors</span>`,
      text: `
       <p>Вітаємо! Ви виграли фірмовий браслет Vapors.</p>
       <p>Приз буде доступний безкоштовно за допомогою промокоду.</p>
       ${LIMIT_TEXT}
      `,

      prizeKey: color,
      showPromoCode: true
    };
  },


  CartridgeGift(win) {
    return {
      title: `<b>Безкоштовний картридж</b> <span>до замовлення!</span>`,
      text: `<p>Додайте будь-який картридж у кошик і отримайте його в подарунок при покупці іншого товару. </p>
            <p>Безкоштовним буде лише 1 картридж.</p>
            ${LIMIT_TEXT}            
            `,

      prizeKey: win,
      showPromoCode: true
    }
  },

  InviteFriend(win) {
    return {
      title: `<span>Скинь другу колесо</span> <b>та отримай 10% знижку</b>`,
      text: `<p>Поділись колесом — друг має зареєструватися та покрутити його.</p>
             <p>Після цього ти отримаєш промокод на 10% у SMS.</p>
             ${LIMIT_TEXT}            
             `,
      prizeKey: win,
      showPromoCode: true
    }
  },

  Liquids(type) {
    const configs = {
      '1_2': {
        title: `<b>Акція 1=2</b> <br> <span>на рідини!</span>`,
        text: `
          <p>Додайте до кошика дві однакові рідини або рідини однієї моделі та бренду — сплатіть лише за одну, другу отримаєте у подарунок!</p>
          ${LIMIT_TEXT}
          <a class="fortune__info-action-btn button-light-blue" href="/nabori-solov-3013/">Обрати товари</a>
        `
      },

      '1_1_3': {
        title: `<b>Акція 1+1=3</b> <br> <span>на рідини!</span>`,
        text: `
            <p>Додайте до кошика три однакові рідини або рідини однієї моделі та бренду — сплатіть лише за дві, третя буде у подарунок!</p>
            ${LIMIT_TEXT}
          <a class="fortune__info-action-btn button-light-blue" href="/nabori-solov-3013/">Обрати товари</a>
        `
      }
    };

    const config = configs[type];

    if (!config) {
      console.warn("Unknown liquids type:", type);
      return {};
    }

    return {
      title: config.title,
      text: config.text,
      prizeKey: type,
      showPromoCode: false
    };
  },

  Merch(type) {
    const MERCH = {
      cosmetic: {
        title: `<b>Фірмовий мерч від Oxva — </b><span>косметичка</span>`,
        textStart: `Ви виграли стильну фірмову косметичку Oxva!`
      },
      bandana: {
        title: `<b>Фірмовий мерч від Oxva — </b><span>бандана</span>`,
        textStart: `Ваш приз — фірмова бандана Oxva!`
      },
      keychain: {
        title: `<b>Фірмовий мерч від Oxva — </b><span>брелок</span>`,
        textStart: `Вітаємо! Ви виграли фірмовий брелок Oxva.`
      },
      pen: {
        title: `<b>Фірмова мерч від Vapors — </b> <span>ручка</span>`,
        textStart: `Вітаємо! Ви виграли фірмову ручку Vapors.`
      },
      lanyard: {
        title: `<b>Фірмовий мерч від Punch — </b> <span>ланьярд</span>`,
        textStart: `Вітаємо! Ви виграли фірмовий ланьярд Punch.`
      },
    };

    const data = MERCH[type];

    if (!data) {
      console.warn("Unknown Merch type:", type);
      return {};
    }

    return {
      title: data.title,
      text: `
        <p>${data.textStart}</p>
        <p>Приз буде доступний безкоштовно за допомогою промокоду.</p>
        ${LIMIT_TEXT}
      `,
      prizeKey: type,
      showPromoCode: true
    };
  },

  DoubleCashback() {
    return {
      title: `<b>Подвійний кешбек</b> з покупки`,
      text: `
            <p>Отримайте подвійний кешбек за своє замовлення!</p>
            <p>Промокод уже застосовано до вашого кошика — подвійний кешбек буде нараховано о 00:00 після оплати замовлення.</p>
            ${LIMIT_TEXT}
          `,
      prizeKey: 'double',
      showPromoCode: true
    };
  }


}

function renderPrize({title, text, prizeKey, showPromoCode = false}) {
  $('.js-fortune__prize-title').html(title);
  $('.js-fortune__prize-text').html(text);

  if (prizeKey) {
    ajaxSetPrize(prizeKey);
  }

  if (showPromoCode) $('.fortune__prize-block').css('display', 'flex')
  else $('.fortune__prize-block').css('display', 'none')

}

function ajaxSetPrize(win) {
  $.ajax({
    type: 'POST',
    url: 'index.php?route=extension/module/fortune/win',
    dataType: 'json',
    data: 'win=' + win,
    success: function (json) {
      // console.log(json)
      $('.js-fortune__prize-code').text(json['promo']);

      if (json['success']) {
        console.error(json['success'])
      }
      if (json['error']) {
        console.error(json['error'])
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
    }
  });
  return false;
}




let copyContent = async () => {
  let text = document.getElementById('fortune__prize-code').innerHTML,
    button = document.querySelector('.fortune__prize-copy'),
    icon = document.getElementById('fortune__prize-copy-icon'),
    originalHref = icon.getAttribute('href'); // Сохраняем оригинальное значение href

  try {
    await navigator.clipboard.writeText(text);

    // Меняем href на #icon-check
    icon.setAttribute('href', '#icon-check');
    button.disabled = true;


    // Возвращаем исходное значение href через 5 секунд
    setTimeout(() => {
      icon.setAttribute('href', originalHref);
      button.disabled = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy: ' + err);
  }
};


$('[href="#wheel"]').click(function (e) {
  e.preventDefault();

  $('.btn-wheel-action').trigger('click');
});


/**
 * ТАЙМЕР ОБРАТНОГО ОТСЧЕТА
 */

function updateCountdowns() {
  const now = Date.now();
  const countdowns = document.querySelectorAll('.countdown__items[data-deadline]');

  countdowns.forEach(cd => {
    const deadline = Number(cd.dataset.deadline);
    if (!deadline) return;

    let diff = deadline - now;
    if (diff < 0) diff = 0;

    let sec = Math.floor(diff / 1000);

    const seconds = sec % 60;
    sec = Math.floor(sec / 60);

    const minutes = sec % 60;
    sec = Math.floor(sec / 60);

    const hours = sec % 24;
    const days = Math.floor(sec / 24);

    const dEl = cd.querySelector('.days');
    const hEl = cd.querySelector('.hours');
    const mEl = cd.querySelector('.minutes');
    const sEl = cd.querySelector('.seconds');

    if (dEl) dEl.textContent = days;
    if (hEl) hEl.textContent = String(hours).padStart(2, '0');
    if (mEl) mEl.textContent = String(minutes).padStart(2, '0');
    if (sEl) sEl.textContent = String(seconds).padStart(2, '0');
  });

  setTimeout(updateCountdowns, 1000);
}

updateCountdowns();


// проверка
// const count = {};
// for (let i = 0; i < 10000; i++) {
//   const idx = getRandomSectorIndexByProbability(prizes);
//   const label = prizes[idx].text || prizes[idx].img2x || `Prize ${idx}`;
//   count[label] = (count[label] || 0) + 1;
// }
// console.table(count);
