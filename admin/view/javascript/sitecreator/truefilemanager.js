//true file manager JS; sitecreator.ru (c) 2020

console.log('true file manager JS');

function getURLVar(key) {
  var value = [];
  var href = String(document.location.href);
  href = href.replace(/#.*$/, '');  // удалим хеш, который добавляет elfinder
  var query = href.split('?');

  if (query[1]) {
    var part = query[1].split('&');

    for (i = 0; i < part.length; i++) {
      var data = part[i].split('=');

      if (data[0] && data[1]) {
        value[data[0]] = data[1];
      }
    }

    if (value[key]) {
      return value[key];
    } else {
      return '';
    }
  }
}