<?php
// Ультра-магазин: мерч Vapors за ультравалюту (тест)
class ControllerUltraShop extends Controller
{
    // rarity: common / rare / epic / legendary
    private function items()
    {
        return array(
            array('id' => 'hoodie',   'name' => 'Худі «Neon Cloud»',      'cat' => 'wear', 'price' => 40, 'rarity' => 'legendary', 'stock' => 12,  'desc' => 'Оверсайз худі з неоновою хмаринкою, що світиться в темряві. Лімітована серія.'),
            array('id' => 'tee',      'name' => 'Футболка Vapors',         'cat' => 'wear', 'price' => 20, 'rarity' => 'epic',      'stock' => 40,  'desc' => 'Щільна чорна бавовна, рожевий принт на спині.'),
            array('id' => 'cap',      'name' => 'Кепка «Cyber Pink»',      'cat' => 'wear', 'price' => 15, 'rarity' => 'epic',      'stock' => 30,  'desc' => 'Вишитий логотип, металева застібка.'),
            array('id' => 'socks',    'name' => 'Шкарпетки Vapors',        'cat' => 'wear', 'price' => 5,  'rarity' => 'common',    'stock' => 150, 'desc' => 'Ті самі легендарні шкарпетки — тепер можна обрати пару.'),
            array('id' => 'tote',     'name' => 'Шопер «Cloud Bag»',       'cat' => 'acc',  'price' => 10, 'rarity' => 'rare',      'stock' => 60,  'desc' => 'Місткий шопер з товстої тканини.'),
            array('id' => 'mug',      'name' => 'Чашка «Morning Vape»',    'cat' => 'acc',  'price' => 8,  'rarity' => 'rare',      'stock' => 50,  'desc' => 'Матова чорна чашка з неоновим лого.'),
            array('id' => 'keychain', 'name' => 'Брелок-хмаринка',         'cat' => 'acc',  'price' => 5,  'rarity' => 'rare',      'stock' => 100, 'desc' => 'Акриловий брелок з голографічним ефектом.'),
            array('id' => 'stickers', 'name' => 'Стікерпак Vapors',        'cat' => 'acc',  'price' => 3,  'rarity' => 'common',    'stock' => 300, 'desc' => '10 водостійких наліпок на телефон, ноут чи под.'),
        );
    }

    private function balance()
    {
        if (!$this->customer->isLogged()) return null;
        $this->load->model('extension/module/battlepass');
        return (int)$this->model_extension_module_battlepass->getTotalUltra($this->customer->getId());
    }

    public function index()
    {
        $this->document->setTitle('Ультра-магазин Vapors');
        $data['items'] = $this->items();
        $data['logged'] = $this->customer->isLogged();
        $data['balance'] = $this->balance();
        $data['name'] = $this->customer->isLogged() ? $this->customer->getFirstName() : '';
        $data['login'] = $this->url->link('account/login', '', true);
        $data['buy_url'] = $this->url->link('ultra/shop/buy', '', true);
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
                $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ultra_order` (`order_id` int(11) NOT NULL AUTO_INCREMENT, `customer_id` int(11) NOT NULL, `item` varchar(64) NOT NULL, `name` varchar(255) NOT NULL, `price` int(11) NOT NULL, `status` varchar(32) NOT NULL DEFAULT 'new', `date_added` datetime NOT NULL, PRIMARY KEY (`order_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ultra_order` SET customer_id = '" . $cid . "', item = '" . $this->db->escape($item['id']) . "', name = '" . $this->db->escape($item['name']) . "', price = '" . (int)$item['price'] . "', date_added = NOW()");
                $oid = $this->db->getLastId();
                $this->load->model('extension/module/battlepass');
                $this->model_extension_module_battlepass->addUltra($cid, 'Ультра-магазин: ' . $item['name'] . ' (#' . $oid . ')', -(int)$item['price']);
                $json['success'] = true;
                $json['order'] = $oid;
                $json['balance'] = $this->balance();
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
