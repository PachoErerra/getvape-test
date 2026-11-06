<?php
// Ультра-магазин: мерч Vapors за ультравалюту (тест)
class ControllerUltraShop extends Controller
{
    // rarity: common / rare / epic / legendary
    private function items()
    {
        return array(
            array('id' => 'bomber',   'name' => 'Бомбер «Ultra Legend»',  'cat' => 'wear', 'price' => 120, 'rarity' => 'legendary', 'stock' => 3,   'sizes' => array('S','M','L','XL','XXL'), 'desc' => 'Атласний бомбер з неоновою вишивкою на всю спину. Лише 3 штуки на весь сезон — справжній артефакт.'),
            array('id' => 'sign',     'name' => 'Неонова вивіска Vapors', 'cat' => 'acc',  'price' => 70,  'rarity' => 'mythic',    'stock' => 5,   'desc' => 'Справжній неон з логотипом-хмаринкою. Повісь удома — і кімната стане кіберпанком.'),
            array('id' => 'hoodie',   'name' => 'Худі «Neon Cloud»',      'cat' => 'wear', 'price' => 60,  'rarity' => 'mythic',    'stock' => 12,  'sizes' => array('S','M','L','XL','XXL'), 'desc' => 'Оверсайз худі з неоновою хмаринкою, що світиться в темряві. Лімітована серія.'),
            array('id' => 'tee',      'name' => 'Футболка Vapors',         'cat' => 'wear', 'price' => 20,  'rarity' => 'epic',      'stock' => 40,  'sizes' => array('S','M','L','XL','XXL'), 'desc' => 'Щільна чорна бавовна, рожевий принт на спині.'),
            array('id' => 'cap',      'name' => 'Кепка «Cyber Pink»',      'cat' => 'wear', 'price' => 15,  'rarity' => 'epic',      'stock' => 30,  'desc' => 'Вишитий логотип, металева застібка.'),
            array('id' => 'tote',     'name' => 'Шопер «Cloud Bag»',       'cat' => 'acc',  'price' => 10,  'rarity' => 'rare',      'stock' => 60,  'desc' => 'Місткий шопер з товстої тканини.'),
            array('id' => 'mug',      'name' => 'Чашка «Morning Vape»',    'cat' => 'acc',  'price' => 8,   'rarity' => 'rare',      'stock' => 50,  'desc' => 'Матова чорна чашка з неоновим лого.'),
            array('id' => 'keychain', 'name' => 'Брелок-хмаринка',         'cat' => 'acc',  'price' => 5,   'rarity' => 'rare',      'stock' => 100, 'desc' => 'Акриловий брелок з голографічним ефектом.'),
            array('id' => 'socks',    'name' => 'Шкарпетки Vapors',        'cat' => 'wear', 'price' => 5,   'rarity' => 'rare',      'stock' => 150, 'sizes' => array('36-39','40-43','44-46'), 'desc' => 'Ті самі легендарні шкарпетки — тепер можна обрати пару.'),
            array('id' => 'stickers', 'name' => 'Стікерпак Vapors',        'cat' => 'acc',  'price' => 3,   'rarity' => 'rare',      'stock' => 300, 'desc' => '10 водостійких наліпок на телефон, ноут чи под.'),
        );
    }


    const CASE_PRICE = 10;

    private function ensureTable()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ultra_order` (`order_id` int(11) NOT NULL AUTO_INCREMENT, `customer_id` int(11) NOT NULL, `item` varchar(64) NOT NULL, `name` varchar(255) NOT NULL, `price` int(11) NOT NULL, `status` varchar(32) NOT NULL DEFAULT 'new', `date_added` datetime NOT NULL, PRIMARY KEY (`order_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // рівень гравця: за всю зароблену ультравалюту
    private function profile()
    {
        if (!$this->customer->isLogged()) return null;
        $cid = (int)$this->customer->getId();
        $q = $this->db->query("SELECT COALESCE(SUM(CASE WHEN ultra > 0 THEN ultra ELSE 0 END),0) AS earned FROM `" . DB_PREFIX . "customer_ultra` WHERE customer_id = '" . $cid . "'");
        $earned = (int)$q->row['earned'];
        $lvl = 1; $need = 5; $left = $earned;
        while ($left >= $need) { $left -= $need; $lvl++; $need = 5 + ($lvl - 1) * 5; }
        $this->ensureTable();
        $h = $this->db->query("SELECT order_id, item, name, price, status, date_added FROM `" . DB_PREFIX . "ultra_order` WHERE customer_id = '" . $cid . "' ORDER BY order_id DESC LIMIT 30");
        return array('level' => $lvl, 'xp' => $left, 'need' => $need, 'earned' => $earned, 'history' => $h->rows);
    }

    private function balance()
    {
        if (!$this->customer->isLogged()) return null;
        $this->load->model('extension/module/battlepass');
        return (int)$this->model_extension_module_battlepass->getTotalUltra($this->customer->getId());
    }

    public function index()
    {
        $this->document->setTitle('Ультра шоп Vapors');
        $data['items'] = $this->items();
        $data['logged'] = $this->customer->isLogged();
        $data['balance'] = $this->balance();
        $data['name'] = $this->customer->isLogged() ? $this->customer->getFirstName() : '';
        $data['login'] = $this->url->link('account/login', '', true);
        $data['buy_url'] = $this->url->link('ultra/shop/buy', '', true);
        $data['case_url'] = $this->url->link('ultra/shop/opencase', '', true);
        $data['case_price'] = self::CASE_PRICE;
        $data['profile'] = $this->profile();
        $data['items_json'] = json_encode($this->items(), JSON_UNESCAPED_UNICODE);
        $data['home'] = $this->url->link('common/home');

        $data['column_left'] = '';
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('ultra/shop', $data));
    }

    public function buy()
    {
        $json = array();
        $id = isset($this->request->post['item']) ? (string)$this->request->post['item'] : '';
        $item = null;
        foreach ($this->items() as $it) if ($it['id'] === $id) $item = $it;

        if (!$this->customer->isLogged()) {
            $json['error'] = 'login';
        } elseif (!$item) {
            $json['error'] = 'Такого предмета немає';
        } else {
            $bal = $this->balance();
            if ($bal < $item['price']) {
                $json['error'] = 'Не вистачає ультравалюти: потрібно ' . $item['price'] . ', у тебе ' . $bal;
            } else {
                $cid = (int)$this->customer->getId();
                $this->ensureTable();
                $size = isset($this->request->post['size']) ? trim((string)$this->request->post['size']) : '';
                if ($size !== '' && (empty($item['sizes']) || !in_array($size, $item['sizes'], true))) $size = '';
                $title = $item['name'] . ($size !== '' ? ' (розмір ' . $size . ')' : '');
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ultra_order` SET customer_id = '" . $cid . "', item = '" . $this->db->escape($item['id']) . "', name = '" . $this->db->escape($title) . "', price = '" . (int)$item['price'] . "', date_added = NOW()");
                $oid = $this->db->getLastId();
                $this->load->model('extension/module/battlepass');
                $this->model_extension_module_battlepass->addUltra($cid, 'Ультра-магазин: ' . $title . ' (#' . $oid . ')', -(int)$item['price']);
                $json['success'] = true;
                $json['order'] = $oid;
                $json['balance'] = $this->balance();
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    // Кейс: випадковий предмет за рідкісністю
    public function opencase()
    {
        $json = array();
        if (!$this->customer->isLogged()) {
            $json['error'] = 'login';
        } else {
            $bal = $this->balance();
            if ($bal < self::CASE_PRICE) {
                $json['error'] = 'Не вистачає ультравалюти: кейс коштує ' . self::CASE_PRICE . ', у тебе ' . $bal;
            } else {
                $weights = array('rare' => 700, 'epic' => 220, 'mythic' => 65, 'legendary' => 15);
                $roll = mt_rand(1, 1000); $acc = 0; $rar = 'rare';
                foreach ($weights as $r => $w) { $acc += $w; if ($roll <= $acc) { $rar = $r; break; } }
                $pool = array();
                foreach ($this->items() as $it) if ($it['rarity'] === $rar) $pool[] = $it;
                if (!$pool) $pool = $this->items();
                $item = $pool[mt_rand(0, count($pool) - 1)];
                $cid = (int)$this->customer->getId();
                $this->ensureTable();
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ultra_order` SET customer_id = '" . $cid . "', item = '" . $this->db->escape($item['id']) . "', name = '" . $this->db->escape($item['name'] . ' (з кейса)') . "', price = '" . (int)self::CASE_PRICE . "', date_added = NOW()");
                $oid = $this->db->getLastId();
                $this->load->model('extension/module/battlepass');
                $this->model_extension_module_battlepass->addUltra($cid, 'Ультра-магазин: кейс → ' . $item['name'] . ' (#' . $oid . ')', -self::CASE_PRICE);
                $json['success'] = true; $json['item'] = $item['id']; $json['order'] = $oid; $json['balance'] = $this->balance();
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
