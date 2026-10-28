<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Knowledge base for the School AI assistant.
 *
 * Hosted models cannot be retrained by the app, so the assistant "knows" the software through retrieval:
 * this model indexes the app itself (sidebar menu, routes, controller pages and their form fields, models,
 * database tables, docs and migrations) and stores what it learns later (facts taught by admins and SQL that
 * answered earlier questions). Relevant entries are searched and handed to the AI with each question.
 */
class Ai_knowledge_model extends CI_Model
{
    const TABLE = 'ai_widget_knowledge';
    const MAX_LEARNED_QUERIES = 400;
    const CHECK_EVERY_SECONDS = 600;

    private $auto_sources = array('menu', 'page', 'code', 'model', 'table', 'doc', 'overview');
    private $stop_words = array('the', 'and', 'for', 'are', 'was', 'were', 'what', 'which', 'who', 'whom', 'how', 'why', 'when', 'where', 'can', 'could', 'would', 'should', 'will', 'does', 'did', 'has', 'have', 'had', 'this', 'that', 'these', 'those', 'with', 'from', 'into', 'about', 'there', 'their', 'them', 'they', 'you', 'your', 'our', 'want', 'need', 'please', 'tell', 'show', 'give', 'get', 'all', 'any', 'some', 'many', 'much', 'not', 'yes', 'its', 'his', 'her', 'she', 'him', 'let', 'like', 'just', 'also', 'make', 'use', 'using', 'way', 'able', 'know', 'see', 'find', 'list', 'are', 'more', 'than', 'then', 'only', 'each', 'per', 'out');
    // A few school-software synonyms so plain-language questions find the technical names.
    private $synonyms = array('configure' => 'setting', 'configuration' => 'setting', 'setup' => 'setting', 'settings' => 'setting', 'challan' => 'voucher', 'voucher' => 'challan', 'invoice' => 'receipt', 'teacher' => 'staff', 'employee' => 'staff', 'salary' => 'payroll', 'pupil' => 'student', 'exam' => 'examination', 'marks' => 'result', 'bus' => 'transport', 'van' => 'transport', 'dorm' => 'hostel');

    /* ------------------------------------------------------------------ */
    /* storage                                                             */
    /* ------------------------------------------------------------------ */

    public function ensure_table()
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        if ($this->db->table_exists(self::TABLE)) return;
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . self::TABLE . "` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `source` VARCHAR(20) NOT NULL,
            `topic` VARCHAR(255) NOT NULL,
            `keywords` TEXT NULL,
            `content` MEDIUMTEXT NOT NULL,
            `ref` VARCHAR(255) NULL,
            `created_by` INT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_knowledge_source` (`source`),
            KEY `idx_ai_knowledge_topic` (`topic`(100))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function stats()
    {
        $this->ensure_table();
        $rows = $this->db->select('source, COUNT(*) AS n', false)->from(self::TABLE)->where('source !=', 'meta')->group_by('source')->get()->result_array();
        $by_source = array(); $total = 0;
        foreach ($rows as $row) { $by_source[$row['source']] = (int) $row['n']; $total += (int) $row['n']; }
        $state = $this->state();
        return array('total' => $total, 'by_source' => $by_source, 'built_at' => isset($state['built_at']) ? $state['built_at'] : null);
    }

    /* ------------------------------------------------------------------ */
    /* search                                                              */
    /* ------------------------------------------------------------------ */

    /** Most relevant entries for a question: array of {id, source, topic, content}, trimmed to $max_chars in total. */
    public function search($query, $limit = 5, $max_chars = 2000)
    {
        $this->ensure_table();
        $terms = $this->terms($query, true);
        if (empty($terms)) return array();
        // "Where / how do I ..." questions want the screen to open, not code internals.
        $how_to = (bool) preg_match('/\b(how|where|configure|setting|settings|setup|set up|add|create|change|edit|enable|disable|open|find|print|generate)\b/i', (string) $query);

        $this->db->select('id,source,topic,keywords,content')->from(self::TABLE)->where('source !=', 'meta');
        $this->db->group_start();
        foreach ($terms as $term) { $this->db->or_like('keywords', $term); $this->db->or_like('topic', $term); }
        $this->db->group_end();
        $rows = $this->db->limit(500)->get()->result_array();

        foreach ($rows as $index => $row) {
            $topic = strtolower($row['topic']);
            $keywords = ' ' . strtolower((string) $row['keywords']) . ' ';
            $score = 0; $hits = 0;
            foreach ($terms as $term) {
                $hit = false;
                if (strpos($topic, $term) !== false) { $score += 4; $hit = true; }
                if (strpos($keywords, ' ' . $term) !== false) { $score += 2; $hit = true; }
                if ($hit) $hits++;
            }
            $score += 3 * $hits * $hits; // reward entries that match several words of the question
            if ($row['source'] === 'admin') $score *= 1.6;
            elseif ($how_to && ($row['source'] === 'menu' || $row['source'] === 'page')) $score *= 1.6;
            elseif ($row['source'] === 'learned_query' || $row['source'] === 'menu') $score *= 1.2;
            $rows[$index]['score'] = $score;
        }
        usort($rows, function ($a, $b) { return $b['score'] == $a['score'] ? 0 : ($b['score'] > $a['score'] ? 1 : -1); });

        $out = array(); $used = 0; $per_entry = max(300, (int) ($max_chars / max(1, $limit)) + 150);
        foreach (array_slice($rows, 0, $limit) as $row) {
            if ($row['score'] < 6) break; // single weak keyword hits are noise
            $content = $row['content'];
            if (strlen($content) > $per_entry) $content = substr($content, 0, $per_entry) . '...';
            if ($used + strlen($content) > $max_chars && !empty($out)) break;
            $used += strlen($content);
            $out[] = array('id' => (int) $row['id'], 'source' => $row['source'], 'topic' => $row['topic'], 'content' => $content);
        }
        return $out;
    }

    /** Lower-cased search terms: no stop words, simple plural stemming, optional synonyms. */
    public function terms($text, $with_synonyms = false)
    {
        $words = preg_split('/[^a-z0-9]+/', strtolower(str_replace('_', ' ', (string) $text)));
        $terms = array();
        foreach ($words as $word) {
            if (strlen($word) < 3 || in_array($word, $this->stop_words, true) || ctype_digit($word)) continue;
            if ($with_synonyms && isset($this->synonyms[$word])) $terms[$this->stem($this->synonyms[$word])] = true;
            $terms[$this->stem($word)] = true;
        }
        return array_slice(array_keys($terms), 0, 12);
    }

    private function stem($word)
    {
        if (strlen($word) > 4 && substr($word, -3) === 'ies') return substr($word, 0, -3) . 'y';
        if (strlen($word) > 4 && preg_match('/(ss|x|ch|sh)es$/', $word)) return substr($word, 0, -2);
        if (strlen($word) > 3 && substr($word, -1) === 's' && substr($word, -2) !== 'ss') return substr($word, 0, -1);
        return $word;
    }

    private function keywords_for()
    {
        $words = array();
        foreach (func_get_args() as $text) foreach ($this->terms(is_array($text) ? implode(' ', $text) : $text) as $term) $words[$term] = true;
        return implode(' ', array_keys($words));
    }

    /* ------------------------------------------------------------------ */
    /* learning                                                            */
    /* ------------------------------------------------------------------ */

    /** Stores (or updates, by topic) a fact an administrator taught the assistant. */
    public function remember($topic, $content, $user_id)
    {
        $this->ensure_table();
        $topic = substr(trim(preg_replace('/\s+/', ' ', strip_tags((string) $topic))), 0, 200);
        $content = substr(trim(strip_tags((string) $content)), 0, 2000);
        if ($topic === '' || $content === '') return array('saved' => false, 'error' => 'Both topic and content are required');
        $now = date('Y-m-d H:i:s');
        $existing = $this->db->where(array('source' => 'admin', 'topic' => $topic))->get(self::TABLE)->row_array();
        $data = array('keywords' => $this->keywords_for($topic, $content), 'content' => $content, 'created_by' => (int) $user_id, 'updated_at' => $now);
        if ($existing) { $this->db->where('id', $existing['id'])->update(self::TABLE, $data); return array('saved' => true, 'id' => (int) $existing['id'], 'updated' => true); }
        $this->db->insert(self::TABLE, $data + array('source' => 'admin', 'topic' => $topic, 'created_at' => $now));
        return array('saved' => true, 'id' => (int) $this->db->insert_id(), 'updated' => false);
    }

    /** Deletes a taught fact or a learned query (auto-indexed entries come back on the next rebuild anyway). */
    public function forget($id)
    {
        $this->ensure_table();
        $this->db->where('id', (int) $id)->where_in('source', array('admin', 'learned_query'))->delete(self::TABLE);
        return array('deleted' => $this->db->affected_rows() > 0);
    }

    /** Remembers the SQL that answered a question so the next similar question can reuse it. */
    public function learn_query($question, $sql, $reply, $user_id)
    {
        $this->ensure_table();
        $topic = substr(trim(preg_replace('/\s+/', ' ', (string) $question)), 0, 200);
        if ($topic === '' || trim((string) $sql) === '') return;
        $content = "Question: " . $topic . "\nSQL that answered it: " . trim($sql) . "\nAnswer given: " . substr(trim((string) $reply), 0, 300);
        $now = date('Y-m-d H:i:s');
        $data = array('keywords' => $this->keywords_for($topic, preg_replace('/[^a-z0-9_ ]+/i', ' ', $sql)), 'content' => $content, 'created_by' => (int) $user_id, 'updated_at' => $now);
        $existing = $this->db->where(array('source' => 'learned_query', 'topic' => $topic))->get(self::TABLE)->row_array();
        if ($existing) $this->db->where('id', $existing['id'])->update(self::TABLE, $data);
        else $this->db->insert(self::TABLE, $data + array('source' => 'learned_query', 'topic' => $topic, 'created_at' => $now));

        $count = $this->db->where('source', 'learned_query')->count_all_results(self::TABLE);
        if ($count > self::MAX_LEARNED_QUERIES) {
            $old = $this->db->select('id')->where('source', 'learned_query')->order_by('updated_at', 'ASC')->limit($count - self::MAX_LEARNED_QUERIES)->get(self::TABLE)->result_array();
            if (!empty($old)) $this->db->where_in('id', array_column($old, 'id'))->delete(self::TABLE);
        }
    }

    /* ------------------------------------------------------------------ */
    /* indexing                                                            */
    /* ------------------------------------------------------------------ */

    /** Rebuilds the auto-generated part of the knowledge base when the code changed (checked at most every 10 minutes). */
    public function refresh_if_stale($force = false)
    {
        $this->ensure_table();
        $state = $this->state();
        if (!$force && !empty($state['checked_at']) && time() - (int) $state['checked_at'] < self::CHECK_EVERY_SECONDS) return null;
        $fingerprint = $this->fingerprint();
        if (!$force && !empty($state['fingerprint']) && $state['fingerprint'] === $fingerprint) {
            $state['checked_at'] = time();
            $this->save_state($state);
            return null;
        }
        return $this->rebuild($fingerprint);
    }

    public function rebuild($fingerprint = null)
    {
        @set_time_limit(300);
        $this->ensure_table();
        $started = microtime(true);
        $routes = $this->read_routes();
        $menu = $this->read_menu();
        $model_tables = array();

        $entries = array();
        $entries[] = $this->overview_entry($menu);
        foreach ($menu as $item) $entries[] = $this->menu_entry($item, $routes);
        foreach ($this->controller_entries($menu, $routes) as $entry) $entries[] = $entry;
        foreach ($this->model_entries($model_tables) as $entry) $entries[] = $entry;
        foreach ($this->table_entries($model_tables) as $entry) $entries[] = $entry;
        foreach ($this->doc_entries() as $entry) $entries[] = $entry;

        $now = date('Y-m-d H:i:s');
        $rows = array();
        foreach ($entries as $entry) {
            if (empty($entry)) continue;
            $rows[] = array('source' => $entry['source'], 'topic' => substr($entry['topic'], 0, 255), 'keywords' => substr($entry['keywords'], 0, 60000), 'content' => substr($entry['content'], 0, 4000), 'ref' => isset($entry['ref']) ? substr($entry['ref'], 0, 255) : null, 'created_by' => null, 'created_at' => $now, 'updated_at' => $now);
        }

        $this->db->trans_start();
        $this->db->where_in('source', $this->auto_sources)->delete(self::TABLE);
        foreach (array_chunk($rows, 100) as $chunk) $this->db->insert_batch(self::TABLE, $chunk);
        $this->db->trans_complete();

        $stats = array('entries' => count($rows), 'menu_items' => count($menu), 'seconds' => round(microtime(true) - $started, 2));
        $this->save_state(array('fingerprint' => $fingerprint ?: $this->fingerprint(), 'checked_at' => time(), 'built_at' => $now, 'stats' => $stats));
        return $stats;
    }

    private function state()
    {
        $row = $this->db->select('content')->where(array('source' => 'meta', 'topic' => 'index_state'))->get(self::TABLE)->row_array();
        $state = $row ? json_decode($row['content'], true) : null;
        return is_array($state) ? $state : array();
    }

    private function save_state($state)
    {
        $now = date('Y-m-d H:i:s');
        $json = json_encode($state);
        $exists = $this->db->where(array('source' => 'meta', 'topic' => 'index_state'))->count_all_results(self::TABLE) > 0;
        if ($exists) $this->db->where(array('source' => 'meta', 'topic' => 'index_state'))->update(self::TABLE, array('content' => $json, 'updated_at' => $now));
        else $this->db->insert(self::TABLE, array('source' => 'meta', 'topic' => 'index_state', 'keywords' => '', 'content' => $json, 'created_at' => $now, 'updated_at' => $now));
    }

    private function source_files()
    {
        $files = array_merge(
            array(APPPATH . 'views/layout/sidebar.php', APPPATH . 'config/routes.php'),
            glob(APPPATH . 'controllers/*.php') ?: array(), glob(APPPATH . 'controllers/*/*.php') ?: array(),
            glob(APPPATH . 'models/*.php') ?: array(), glob(APPPATH . 'migrations/*.sql') ?: array(), glob(FCPATH . 'docs/*.md') ?: array()
        );
        return array_values(array_filter($files, 'is_file'));
    }

    private function fingerprint()
    {
        $parts = array();
        foreach ($this->source_files() as $file) $parts[] = $file . ':' . filemtime($file);
        $parts[] = 'tables:' . count($this->db->list_tables());
        return md5(implode('|', $parts));
    }

    /* ---------- PHP source helpers ---------- */

    /** Turns the PHP inside a label (lang lines, string literals) into readable text. */
    private function php_text($text)
    {
        $lang = $this->lang;
        $text = preg_replace_callback('/<\?php\s+echo\s+(.*?)\s*;?\s*\?>/s', function ($m) use ($lang) {
            $out = '';
            if (preg_match_all('/\$this->lang->line\(\s*[\'"]([^\'"]+)[\'"]\s*\)|"([^"]*)"|\'([^\']*)\'/', $m[1], $parts, PREG_SET_ORDER)) {
                foreach ($parts as $part) {
                    if (!empty($part[1])) { $line = $lang->line($part[1]); $out .= $line ? $line : ucwords(str_replace('_', ' ', $part[1])); }
                    elseif (isset($part[3])) $out .= $part[3];
                    else $out .= isset($part[2]) ? $part[2] : '';
                }
            }
            return $out;
        }, (string) $text);
        $text = preg_replace('/<\?php.*?\?>/s', '', $text);
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8')), " \t*:");
    }

    /** Readable label text, or '' for labels built by JavaScript/PHP that cannot be read statically. */
    private function clean_label($html)
    {
        $text = trim($this->php_text($html), " \t\"'>");
        if ($text === '' || strlen($text) > 80 || preg_match('/[+{}$]|\'\s*\+|\+\s*\'/', $text) || !preg_match('/[a-z]/i', $text)) return '';
        return $text;
    }

    private function href_path($href)
    {
        if (preg_match('/site_url\(\s*[\'"]([^\'"]*)[\'"]/', $href, $m)) return trim($m[1], '/');
        if (preg_match('/base_url\(\)[^?]*\?>\s*(.*)$/', $href, $m)) return trim($m[1], '/');
        return trim(preg_replace('/<\?php.*?\?>/', '', $href), '/');
    }

    private function read_routes()
    {
        $routes = array();
        $file = APPPATH . 'config/routes.php';
        if (!is_file($file)) return $routes;
        if (preg_match_all('/\$route\[\s*[\'"]([^\'"]+)[\'"]\s*\]\s*=\s*[\'"]([^\'"]+)[\'"]/', file_get_contents($file), $m, PREG_SET_ORDER)) {
            foreach ($m as $route) $routes[strtolower($route[1])] = strtolower($route[2]);
        }
        return $routes;
    }

    /** Sidebar menu as a list of {section, label, path}. */
    private function read_menu()
    {
        $file = APPPATH . 'views/layout/sidebar.php';
        if (!is_file($file)) return array();
        $menu = array(); $section = ''; $pending_section = false;
        foreach (preg_split('/\R/', file_get_contents($file)) as $line) {
            if (preg_match('/set_Topmenu\(/', $line)) { $pending_section = true; continue; }
            if ($pending_section && preg_match('/<span>(.*?)<\/span>/', $line, $m)) { $section = $this->php_text($m[1]); $pending_section = false; continue; }
            if (preg_match_all('/<a\s+href="([^"]*)"[^>]*>(.*?)<\/a>/', $line, $links, PREG_SET_ORDER)) {
                foreach ($links as $link) {
                    $path = $this->href_path($link[1]);
                    $label = $this->php_text($link[2]);
                    if ($path === '' || $path === '#' || $label === '') continue;
                    $menu[] = array('section' => $section, 'label' => $label, 'path' => $path);
                }
            }
        }
        return $menu;
    }

    /** Maps a URL path to {file, class, method}, following custom routes first. */
    private function resolve_path($path, $routes)
    {
        $path = strtolower(trim($path, '/'));
        if (isset($routes[$path])) $path = $routes[$path];
        $segments = explode('/', $path);
        if (count($segments) >= 2 && is_file(APPPATH . 'controllers/' . $segments[0] . '/' . ucfirst($segments[1]) . '.php')) {
            return array('class' => ucfirst($segments[1]), 'url' => $segments[0] . '/' . $segments[1], 'method' => isset($segments[2]) ? $segments[2] : 'index');
        }
        if (is_file(APPPATH . 'controllers/' . ucfirst($segments[0]) . '.php')) {
            return array('class' => ucfirst($segments[0]), 'url' => $segments[0], 'method' => isset($segments[1]) ? $segments[1] : 'index');
        }
        return null;
    }

    /* ---------- entries ---------- */

    private function overview_entry($menu)
    {
        $sections = array();
        foreach ($menu as $item) if ($item['section'] !== '') $sections[$item['section']] = true;
        $school = method_exists($this->setting_model, 'getCurrentSchoolName') ? $this->setting_model->getCurrentSchoolName() : '';
        $content = 'This is the ' . ($school ? $school . ' ' : '') . 'school management system (Smart School, CodeIgniter 3 + MySQL). '
            . 'The admin panel is organised in these sidebar sections: ' . implode(', ', array_keys($sections)) . '. '
            . 'Every page is opened from the left sidebar; System Settings holds school-wide configuration. Fee vouchers are also called challans.';
        return array('source' => 'overview', 'topic' => 'Overview of the school software and its modules', 'keywords' => $this->keywords_for('overview modules menu sections software system', array_keys($sections)), 'content' => $content);
    }

    private function menu_entry($item, $routes)
    {
        $target = $this->resolve_path($item['path'], $routes);
        $where = $item['section'] !== '' ? $item['section'] . ' > ' . $item['label'] : $item['label'];
        $content = 'To open "' . $item['label'] . '": in the left sidebar click ' . $where . '. Page URL: /' . $item['path'] . '.';
        if ($target) $content .= ' Handled by controller ' . $target['class'] . '::' . $target['method'] . '().';
        return array('source' => 'menu', 'topic' => 'Menu: ' . $where, 'keywords' => $this->keywords_for($item['section'], $item['label'], $item['path'], $target ? $target['class'] : ''), 'content' => $content, 'ref' => $item['path']);
    }

    private function controller_entries($menu, $routes)
    {
        $menu_by_target = array();
        foreach ($menu as $item) {
            $target = $this->resolve_path($item['path'], $routes);
            if ($target) $menu_by_target[strtolower($target['class'] . '::' . $target['method'])] = ($item['section'] !== '' ? $item['section'] . ' > ' : '') . $item['label'];
        }
        $route_by_target = array();
        foreach ($routes as $from => $to) $route_by_target[$to] = $from;

        $entries = array();
        $files = array_merge(glob(APPPATH . 'controllers/*.php') ?: array(), glob(APPPATH . 'controllers/*/*.php') ?: array());
        foreach ($files as $file) {
            $code = file_get_contents($file);
            if (!preg_match('/class\s+(\w+)\s+extends\s+(\w+)/', $code, $class)) continue;
            $dir = basename(dirname($file));
            $url = ($dir === 'controllers' ? '' : strtolower($dir) . '/') . strtolower($class[1]);
            if (!preg_match_all('/(\/\*\*(?:(?!\*\/).)*\*\/\s*)?public\s+function\s+(\w+)\s*\(/s', $code, $methods, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) continue;

            $summary = array();
            foreach ($methods as $i => $method) {
                $name = $method[2][0];
                if ($name === '__construct' || $name[0] === '_') continue;
                $start = $method[0][1];
                $end = isset($methods[$i + 1]) ? $methods[$i + 1][0][1] : strlen($code);
                $body = substr($code, $start, $end - $start);
                $doc = '';
                if (!empty($method[1][0]) && preg_match('/\*\s*([^@*\s][^\n]*)/', $method[1][0], $d)) $doc = trim($d[1]);
                $method_url = $url . ($name === 'index' ? '' : '/' . strtolower($name));
                $route = isset($route_by_target[$url . '/' . strtolower($name)]) ? $route_by_target[$url . '/' . strtolower($name)] : null;

                $views = array();
                if (preg_match_all('/load->view\(\s*[\'"]([^\'"]+)[\'"]/', $body, $v)) foreach ($v[1] as $view) if (strpos($view, 'layout/') !== 0) $views[$view] = true;
                $models = array();
                if (preg_match_all('/\$this->(\w+_model)->/i', $body, $mm)) foreach ($mm[1] as $model) $models[strtolower($model)] = true;
                $summary[] = '- /' . $method_url . ($doc ? ': ' . $doc : '') . (!empty($views) ? ' [page]' : '');

                if (empty($views)) continue; // only methods that render a page get their own entry
                $menu_label = isset($menu_by_target[strtolower($class[1] . '::' . $name)]) ? $menu_by_target[strtolower($class[1] . '::' . $name)] : '';
                $fields = array(); $titles = array();
                foreach (array_keys($views) as $view) {
                    $view_file = APPPATH . 'views/' . $view . '.php';
                    if (!is_file($view_file)) continue;
                    $html = file_get_contents($view_file);
                    if (preg_match_all('/<label[^>]*>(.*?)<\/label>/s', $html, $labels)) foreach ($labels[1] as $label) { $text = $this->clean_label($label); if ($text !== '') $fields[$text] = true; }
                    if (preg_match_all('/class="box-title"[^>]*>(.*?)<\/h3>/s', $html, $h)) foreach ($h[1] as $title) { $text = $this->clean_label($title); if ($text !== '') $titles[$text] = true; }
                }
                $fields = array_slice(array_keys($fields), 0, 30);
                $titles = array_slice(array_keys($titles), 0, 5);

                $content = 'Page /' . ($route ? $route : $method_url) . ($menu_label ? ' (sidebar: ' . $menu_label . ')' : '') . '. Controller ' . $class[1] . '::' . $name . '()' . ($doc ? ' - ' . $doc : '') . '.';
                if (!empty($titles)) $content .= ' Sections on the page: ' . implode('; ', $titles) . '.';
                if (!empty($fields)) $content .= ' Fields you can fill or change: ' . implode('; ', $fields) . '.';
                if (!empty($models)) $content .= ' Data handled by: ' . implode(', ', array_keys($models)) . '.';
                $content .= ' View file: ' . implode(', ', array_keys($views)) . '.';
                $entries[] = array('source' => 'page', 'topic' => 'Page: ' . ($menu_label ? $menu_label : $class[1] . ' ' . $name), 'keywords' => $this->keywords_for($class[1], $name, $menu_label, $titles, $fields, $route ? $route : $method_url, array_keys($views)), 'content' => $content, 'ref' => $method_url);
            }
            if (!empty($summary)) {
                $entries[] = array('source' => 'code', 'topic' => 'Controller ' . $class[1] . ' (/' . $url . ')', 'keywords' => $this->keywords_for($class[1], $url, implode(' ', $summary)), 'content' => 'Controller ' . $class[1] . ' in ' . str_replace(APPPATH, 'application/', $file) . '. URLs:' . "\n" . implode("\n", array_slice($summary, 0, 60)), 'ref' => $url);
            }
        }
        return $entries;
    }

    private function model_entries(&$model_tables)
    {
        $entries = array();
        foreach (glob(APPPATH . 'models/*.php') ?: array() as $file) {
            $code = file_get_contents($file);
            if (!preg_match('/class\s+(\w+)/', $code, $class)) continue;
            $tables = array();
            if (preg_match_all('/(?:->from|->get|->get_where|->insert|->insert_batch|->update|->update_batch|->delete|->join|->count_all|->count_all_results|->replace|table_exists)\(\s*[\'"]`?([a-z][a-z0-9_]*)`?/i', $code, $t)) foreach ($t[1] as $table) $tables[strtolower($table)] = true;
            if (preg_match_all('/\b(?:FROM|JOIN|INTO|UPDATE)\s+`?([a-z][a-z0-9_]{2,})`?/i', $code, $t)) foreach ($t[1] as $table) if ($this->db->table_exists($table)) $tables[strtolower($table)] = true;
            $methods = array();
            if (preg_match_all('/public\s+function\s+(\w+)\s*\(/', $code, $mm)) foreach ($mm[1] as $method) if ($method !== '__construct') $methods[] = $method;
            foreach (array_keys($tables) as $table) $model_tables[$table][] = $class[1];
            $entries[] = array('source' => 'model', 'topic' => 'Model ' . $class[1], 'keywords' => $this->keywords_for($class[1], array_keys($tables), $methods), 'content' => 'Model ' . $class[1] . ' (application/models/' . basename($file) . '). Tables: ' . (empty($tables) ? 'none found' : implode(', ', array_keys($tables))) . '. Functions: ' . implode(', ', array_slice($methods, 0, 60)) . '.', 'ref' => basename($file));
        }
        return $entries;
    }

    private function table_entries($model_tables)
    {
        $entries = array();
        foreach ($this->db->list_tables() as $table) {
            $lower = strtolower($table);
            if ($lower === 'ci_sessions' || strpos($lower, 'ai_widget_') === 0) continue;
            $fields = array();
            foreach ($this->db->list_fields($table) as $field) if (!preg_match('/password|passcode|token|secret|api[_-]?key|auth[_-]?key|salt/i', $field)) $fields[] = $field;
            $used_by = isset($model_tables[$lower]) ? array_unique($model_tables[$lower]) : array();
            $entries[] = array('source' => 'table', 'topic' => 'Table ' . $table, 'keywords' => $this->keywords_for($table, $fields, $used_by), 'content' => 'Database table ' . $table . '. Columns: ' . implode(', ', array_slice($fields, 0, 80)) . '.' . (!empty($used_by) ? ' Used by: ' . implode(', ', array_slice($used_by, 0, 10)) . '.' : ''), 'ref' => $table);
        }
        return $entries;
    }

    private function doc_entries()
    {
        $entries = array();
        foreach (glob(FCPATH . 'docs/*.md') ?: array() as $file) {
            $parts = preg_split('/^(?=#{1,3}\s)/m', file_get_contents($file));
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') continue;
                $heading = preg_match('/^#{1,3}\s+(.+)$/m', $part, $h) ? trim($h[1]) : basename($file);
                $entries[] = array('source' => 'doc', 'topic' => 'Doc ' . basename($file) . ': ' . $heading, 'keywords' => $this->keywords_for($heading, $part), 'content' => $part, 'ref' => 'docs/' . basename($file));
            }
        }
        foreach (glob(APPPATH . 'migrations/*.sql') ?: array() as $file) {
            $sql = file_get_contents($file);
            $comments = array();
            if (preg_match_all('/^\s*--\s?(.*)$/m', $sql, $c)) $comments = array_slice(array_filter(array_map('trim', $c[1])), 0, 6);
            $tables = array();
            if (preg_match_all('/CREATE TABLE IF NOT EXISTS\s+`?(\w+)`?|ALTER TABLE\s+`?(\w+)`?/i', $sql, $t, PREG_SET_ORDER)) foreach ($t as $match) $tables[$match[1] ?: $match[2]] = true;
            $content = 'Database migration application/migrations/' . basename($file) . '. ' . implode(' ', $comments) . (!empty($tables) ? ' Tables created or changed: ' . implode(', ', array_keys($tables)) . '.' : '') . ' If one of these tables is missing, this file has not been imported yet (import it in phpMyAdmin).';
            $entries[] = array('source' => 'doc', 'topic' => 'Migration ' . basename($file), 'keywords' => $this->keywords_for(basename($file, '.sql'), $comments, array_keys($tables)), 'content' => $content, 'ref' => basename($file));
        }
        return $entries;
    }
}
