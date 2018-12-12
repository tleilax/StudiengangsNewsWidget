<?php
/**
 * StudiengangsNewsWidget.class.php
 *
 * @author  Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 * @version 1.0
 */
class StudiengangsNewsWidget extends StudIPPlugin implements PortalPlugin
{
    const GETTEXT_DOMAIN = 'studiengang-news-widget';

    public function __construct()
    {
        parent::__construct();

        bindtextdomain(static::GETTEXT_DOMAIN, $this->getPluginPath() . '/locale');
        bind_textdomain_codeset(static::GETTEXT_DOMAIN, 'UTF-8');
        StudipAutoloader::addAutoloadPath($this->getPluginPath() . '/classes', 'StudiengangsNews');

        $this->is_root = $GLOBALS['perm']->have_perm('root');
        if ($this->is_root) {
            $faculties = Institute::findBySQL('Institut_id = fakultaets_id AND type = 7 ORDER BY Name');
        } else {
            $faculties = Institute::findBySQL('JOIN roles_user ON (Institute.Institut_id = roles_user.Institut_id)
            JOIN roles ON (roles.roleid = roles_user.roleid)
            WHERE roles.rolename = :rolename AND roles_user.userid = :user_id
                ORDER BY Institute.Name', [':rolename' => 'stgnews_admin', ':user_id' => $GLOBALS['user']->user_id]);
        }

        $this->faculties = [];
        foreach ($faculties as $fac) {
            if (count($fac->sub_institutes) > 0) {
                $tmp_inst = [];
                foreach ($fac->sub_institutes as $sub_fac) {
                    if (Studiengang::countBySQL('institut_id = ?', [$sub_fac->institut_id])) {
                        $tmp_inst[$sub_fac->institut_id] = $sub_fac->toArray();
                    }
                }
                if (!empty($tmp_inst) > 0) {
                    $this->faculties[$fac->institut_id] = $fac->toArray();
                    $this->faculties[$fac->institut_id]['sub'] = $tmp_inst;
                }
            } else {
                if (Studiengang::countBySQL('institut_id = ?', [$fac->institut_id])) {
                    if (!isset($this->faculties[$fac->faculty->institut_id])) {
                        $this->faculties[$fac->faculty->institut_id] = $fac->faculty->toArray();
                    }
                    if (!isset($this->faculties[$fac->faculty->institut_id]['sub'][$fac->institut_id])) {
                        $this->faculties[$fac->faculty->institut_id]['sub'][$fac->institut_id] = $fac->toArray();
                    }
                }
            }
        }

        if (!isset($_SESSION['old_studycourse_news'])) {
            $_SESSION['old_studycourse_news'] = 0;
        }
        $_SESSION['old_studycourse_news'] = Request::get('old_studycourse_news', $_SESSION['old_studycourse_news']);

        if (!empty($this->faculties)) {
            usort($this->faculties, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        }

        $this->is_admin  = !empty($this->faculties);  // True when the user can post news.
        PageLayout::addScript($this->getPluginURL() . '/assets/studiengangsnewswidget.js');
    }

    /**
     * Plugin localization for a single string.
     * This method supports sprintf()-like execution if you pass additional
     * parameters.
     *
     * @param String $string String to translate
     * @return translated string
     */
    public function _($string)
    {
        $result = static::GETTEXT_DOMAIN === null
                ? $string
                : dcgettext(static::GETTEXT_DOMAIN, $string, LC_MESSAGES);
        if ($result === $string) {
            $result = _($string);
        }

        if (func_num_args() > 1) {
            $arguments = array_slice(func_get_args(), 1);
            $result = vsprintf($result, $arguments);
        }

        return $result;
    }

    /**
     * Plugin localization for plural strings.
     * This method supports sprintf()-like execution if you pass additional
     * parameters.
     *
     * @param String $string0 String to translate (singular)
     * @param String $string1 String to translate (plural)
     * @param mixed  $n       Quantity factor (may be an array or array-like)
     * @return translated string
     */
    public function _n($string0, $string1, $n)
    {
        if (is_array($n)) {
            $n = count($n);
        }

        $result = static::GETTEXT_DOMAIN === null
                ? $string0
                : dngettext(static::GETTEXT_DOMAIN, $string0, $string1, $n);
        if ($result === $string0 || $result === $string1) {
            $result = ngettext($string0, $string1, $n);
        }

        if (func_num_args() > 3) {
            $arguments = array_slice(func_get_args(), 3);
            $result = vsprintf($result, $arguments);
        }

        return $result;
    }

    /**
     * @return string the plugin name
     */
    public function getPluginName()
    {
        return Config::get()->STG_NEWS_WIDGET_TITLE
            ?: $this->_('Neuigkeiten zu Ihren Studiengängen');
    }

    /**
     * @return array Navigation
     */
    protected function getNavigation()
    {
        $navigation = [];

        if ($this->is_admin) {
            $nav = new Navigation('', PluginEngine::getLink($this, [], 'edit'));
            $nav->setImage(Icon::create('add', 'clickable') , tooltip2($this->_('Eintrag hinzufügen')) + ['data-dialog' => '']);
            $navigation[] = $nav;
        }
        if($this->is_root) {
            $nav = new Navigation('', PluginEngine::getLink($this, [], 'settings'));
            $nav->setImage(Icon::create('admin', 'clickable'), tooltip2($this->_('Einstellungen')) + ['data-dialog' => 'size=auto']);
            $navigation[] = $nav;
        }
        return $navigation;
    }

    /**
     * Returns the widget that is displayed on the start page.
     * @return mixed
     */
    public function getPortalTemplate()
    {
        $this->addStylesheet('assets/studiengangsnewswidget.less');
        $widget = $GLOBALS['template_factory']->open('shared/string');

        $widget->icons = $this->getNavigation();
        $widget->title = $this->getPluginName();

        if ($this->is_admin) {
            $widget->content = $this->getContent();
        } else {
            $widget->content = $this->getContentForStudent();
        }
        return $widget;
    }

    /**
     * Fetches the entries for a set of study courses.
     * @param StudiengangsNews\StudyCourse $study_courses
     * @return mixed
     */
    protected function getContent()
    {
        $template = $this->getTemplate('widget.php');
        $template->is_admin = $this->is_admin;
        $template->faculties = $this->faculties;
        $inst_ids = [];

        foreach ($this->faculties as $id => $faculty) {
            foreach ($this->faculties[$id]['sub'] as $id => $inst) {
                $inst_ids[] = $id;
            }
        }

        if ($GLOBALS['perm']->have_perm('root') && $_SESSION['old_studycourse_news']) {
            $template->studiengaenge = Studiengang::findBySQL(
                    'JOIN news_range ON (mvv_studiengang.studiengang_id = news_range.range_id)
                    JOIN news ON (news.news_id = news_range.news_id)
                    WHERE Institut_id IN (:inst_ids)
                    AND :time > news.date + expire
                    GROUP BY mvv_studiengang.studiengang_id ORDER BY name ',
                [':inst_ids' => $inst_ids, ':time' => time()]);
        } else {
            $template->studiengaenge = Studiengang::findBySQL(
                    'JOIN news_range ON (mvv_studiengang.studiengang_id = news_range.range_id)
                    JOIN news ON (news.news_id = news_range.news_id)
                    WHERE Institut_id IN (:inst_ids)
                    AND :time <= news.date + expire
                    GROUP BY mvv_studiengang.studiengang_id ORDER BY name ',
                [':inst_ids' => $inst_ids, ':time' => time()]);
        }

        return $template->render();
    }

    /**
     * Fetches the entries for a given student.
     * @return mixed
     */
    protected function getContentForStudent()
    {
        $template = $this->getTemplate('widget.php');
        $template->is_admin = $this->is_admin;

        $foo = [];
        $foo_studiengaenge = [];
        foreach (UserStudyCourse::findByUser($GLOBALS['user']->user_id) as $user_studycourse) {
            foreach(Studiengang::findByFachAbschluss($user_studycourse->fach_id, $user_studycourse->abschluss_id) as $stg) {
                $foo_studiengaenge[] = $stg;
            }
        }
        $template->studiengaenge = $foo_studiengaenge;
        if (count($template->studiengaenge) > 0) {
            foreach ($template->studiengaenge as $t) {
                $news_tmp = StudipNews::GetNewsByRange($t->studiengang_id, true, true);
                if (count($news_tmp) > 0) {
                    $foo[$t->studiengang_id] = $news_tmp;
                }
            }
        }
        $template->news = $foo;
        return $template->render();
    }

    /**
     * XHR: Fetches the entries for a given study course.
     * @param $abschl_stg_combo
     */
    public function get_entries_action($studiengang_id)
    {
        if(!$this->is_admin) {
            return;
        }
        $template = $this->getTemplate('news.php');
        $template->controller = $this;
        $template->studiengang = Studiengang::find($studiengang_id);
        if ($GLOBALS['perm']->have_perm('root') && $_SESSION['old_studycourse_news']) {
            $template->news = StudipNews::findBySQL(
                      'JOIN news_range USING (news_id)
                      WHERE range_id = :range_id
                      AND :time > news.date + news.expire
                      ORDER BY date DESC, chdate DESC, topic ASC',
                  [':time' => time(), ':range_id' => $studiengang_id]);
        } else {
            $template->news = StudipNews::GetNewsByRange($studiengang_id, true, true);
        }

        echo $template->render();
    }

    /**
     * XHR: Shows degree/subject boxes.
     * @param $path
     * @param $fk_ids
     */
    public function table_action($fk_ids, $news_id = '')
    {
        if(!$this->is_admin) {
            return;
        }

        $template = $this->getTemplate('_studycourses');
        $template->test = explode('_', $fk_ids);
        $template->bla = $fk_ids;
        $template->studycourses = SimpleCollection::createFromArray(Studiengang::findBySQL('institut_id IN (:inst_ids) AND stat = :status ORDER BY abschluss_id, name',
            [':inst_ids' => explode('_', $fk_ids), ':status' => 'genehmigt']));

        $news = StudipNews::find($news_id);
        if (!is_null($news)) {
            $template->selected_study_courses = $news->news_ranges->pluck('range_id');
        } else {
            $template->selected_study_courses = [];
        }
        $template->graduation_id = array_unique($template->studycourses->pluck('abschluss_id'));
        echo $template->render();
    }

    /**
     * Edits an entry.
     * @param $id
     * @throws AccessDeniedException
     */
    public function edit_action($id = null)
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }

        $template = $this->getTemplate('edit', true);
        $template->faculties = $this->faculties;
        $template->entry = new StudipNews($id);

        $template->study_courses = SimpleCollection::createFromArray(Studiengang::findBySQL('studiengang_id IN (:ids) ORDER BY name',
                [':ids' => $template->entry->news_ranges->pluck('range_id')]));
        $template->all_study_courses = SimpleCollection::createFromArray(Studiengang::findBySQL('institut_id IN (:ids) ORDER BY name',
                [':ids' => $template->study_courses->pluck('institut_id')]));

        echo $template->render();
    }

    public function delete_action ($news_id)
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }
        $news = StudipNews::find($news_id);
        if (!is_null($news)) {
            if ($news->delete()) {
                PageLayout::postSuccess($this->_('Eintrag erfolgreich gelöscht.'));
            } else {
                PageLayout::postError($this->_('Fehler beim Löschen der Eintrags!'));
            }
        } else {
            PageLayout::postError($this->sprintf(_('Kein Eintrag mit der ID %s gefunden.'), $news_id));
        }
        header('Location: ' . URLHelper::getLink('dispatch.php/start'));
    }

    /**
     * Stores an entry.
     * @param null $id
     * @throws AccessDeniedException
     * @throws InvalidMethodException
     */
    public function store_action($id = null)
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }

        if (!Request::isPost()) {
            throw new InvalidMethodException();
        }

        $institution_ids = Request::getArray('faculty_id');
        $studycourse_ids = Request::getArray('studycourse_ids');

        if (empty($institution_ids) || empty($studycourse_ids)) {
            PageLayout::postError($this->_('Fehler beim Speichern der Ankündigung. Es wurde kein Studiengang ausgewählt.'));
        } else {
            $news = StudipNews::find($id);
            if (is_null($news)) {
                $news = new StudipNews();
                $news->id = $news->getNewId();
                $news->mkdate = time();
            } else {
                if (!$news->isNew() && $news->user_id != $GLOBALS['user']->id) {
                    $news->chdate_uid = $GLOBALS['user']->id;
                }
            }
            $news->topic = Request::get('subject');
            $news->body = Request::get('content');
            $news->user_id = $GLOBALS['user']->id;
            $news->author = $GLOBALS['user']->getFullname();
            $news->allow_comments = 0;
            $news->chdate = time();
            $news->date = time();
            $news->expire = strtotime(Request::get('expires') . ' 23:59:59') - $news->date;

            $ranges = [];
            foreach ($studycourse_ids as $studycourse_id)
            {
                $news_range = NewsRange::find([$news->id, $studycourse_id]);
                if (is_null($news_range)) {
                    $news_range = new NewsRange();
                    $news_range->news_id = $news->id;
                    $news_range->range_id = $studycourse_id;
                }
                $ranges[] = $news_range;
            }
            $news->news_ranges = SimpleORMapCollection::createFromArray($ranges);
            if ($news->store()) {
                PageLayout::postSuccess($this->_('Neuigkeit erfolgreich gespeichert'));
            } else {
                PageLayout::postError($this->_('Fehler beim Speichern der Neuigkeit'));
            }
        }
        header('Location: ' . URLHelper::getLink('dispatch.php/start'));
    }

    /**
     * Set's 'visited' flag.
     */
    public function visit_action($news_id)
    {
        object_set_visit($news_id, 'news', $GLOBALS['user']->id);
        object_add_view($news_id);
        echo object_return_views($news_id);
    }

    /**
     * Shows all currently visible news for a given study course.
     * @param $abschluss_id
     * @param $fach_id
     * @throws AccessDeniedException
     */
    public function content_action($abschluss_id, $fach_id)
    {
        if (!$this->is_root) {
            throw new AccessDeniedException;
        }
        echo $this->getContent(new StudiengangsNews\StudyCourse([], [$abschluss_id], [$fach_id]));
    }

    /**
     * Shows settings page.
     * @throws AccessDeniedException
     */
    public function settings_action()
    {
        if (!$this->is_root) {
            throw new AccessDeniedException();
        }

        $this->setPageTitle($this->_('Einstellungen'));

        if (Request::isPost()) {
            $title = Request::get('title', $this->_('Neuigkeiten zu Ihren Studiengängen'));
            $title = trim($title);

            Config::get()->store('STG_NEWS_WIDGET_TITLE', $title);

            PageLayout::postSuccess($this->_('Die Einstellungen wurden gespeichert.'));
            header('Location: ' . URLHelper::getURL('dispatch.php/start'));
            return;
        }

        $template = $this->getTemplate('settings.php', true);
        $template->title = Config::get()->STG_NEWS_WIDGET_TITLE;
        echo $template->render();
    }

    /**
     * @param $template
     * @param bool $layout
     * @return mixed
     */
    protected function getTemplate($template, $layout = false)
    {
        if (Request::isXhr()) {
            header('Content-Type: text/html;charset=utf-8');
            header('X-Initialize-Dialog: true');
        }

        $factory  = new Flexi_TemplateFactory(__DIR__ . '/views');
        $template = $factory->open($template);
        $template->controller = $this;
        $template->_          = function ($string) { return $this->_($string); };
        if ($layout && !Request::isXhr()) {
            $template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
        }
        return $template;
    }

    /**
     * @param $title
     */
    public function setPageTitle($title)
    {
        $args = array_slice(func_get_args(), 1);
        $title = vsprintf($title, $args);
        PageLayout::setTitle($title);
    }

    /**
     * @param $to
     * @return mixed
     */
    public function url_for($to)
    {
        $arguments = func_get_args();
        $last = end($arguments);
        if (is_array($last)) {
            $params = array_pop($arguments);
        } else {
            $params = [];
        }

        $path = implode('/', $arguments);

        return PluginEngine::getURL($this, $params, $path);
    }
}
