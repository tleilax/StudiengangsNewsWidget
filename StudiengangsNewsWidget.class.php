<?php
/**
 * StudiengangsNewsWidget.class.php
 *
 * @author  Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 * @version 1.0
 */
class StudiengangsNewsWidget extends StudIPPlugin implements PortalPlugin
{
    public function __construct()
    {
        parent::__construct();

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
            } elseif (Studiengang::countBySQL('institut_id = ?', [$fac->institut_id])) {
                if (!isset($this->faculties[$fac->faculty->institut_id])) {
                    $this->faculties[$fac->faculty->institut_id] = $fac->faculty->toArray();
                }
                if (!isset($this->faculties[$fac->faculty->institut_id]['sub'][$fac->institut_id])) {
                    $this->faculties[$fac->faculty->institut_id]['sub'][$fac->institut_id] = $fac->toArray();
                }
            }
        }

        if (!empty($this->faculties)) {
            usort($this->faculties, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        }

        $_SESSION['old_studycourse_news'] = Request::get(
            'old_studycourse_news',
            $_SESSION['old_studycourse_news'] ?? 0
        );

        $this->is_admin = !empty($this->faculties);  // True when the user can post news.
        $this->addScript('assets/studiengangsnewswidget.js');
    }

    /**
     * @return string the plugin name
     */
    public function getPluginName()
    {
        return $this->_('Neuigkeiten zu Ihren Studiengängen');
    }

    /**
     * @return array Navigation
     */
    protected function getNavigation()
    {
        $navigation = [];

        if ($this->is_admin) {
            $nav = new Navigation('', PluginEngine::getURL($this, [], 'edit'));
            $nav->setImage(Icon::create('add') , tooltip2($this->_('Eintrag hinzufügen')) + ['data-dialog' => '']);
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
        $this->addStylesheet('assets/studiengangsnewswidget.scss');
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
     */
    protected function getContent(): string
    {
        $template = $this->getTemplate('widget.php');
        $template->is_admin = $this->is_admin;
        $template->faculties = $this->faculties;

        $inst_ids = [];
        foreach ($this->faculties as $id => $faculty) {
            $inst_ids = array_merge(
                $inst_ids,
                array_keys($this->faculties[$id]['sub'])
            );
        }

        if (
            $GLOBALS['perm']->have_perm('root')
            && !empty($_SESSION['old_studycourse_news'])
        ) {
            $template->studiengaenge = Studiengang::findBySQL(
                "JOIN news_range ON (mvv_studiengang.studiengang_id = news_range.range_id)
                JOIN news ON (news.news_id = news_range.news_id)
                WHERE Institut_id IN (:inst_ids)
                AND UNIX_TIMESTAMP() > news.date + expire
                GROUP BY mvv_studiengang.studiengang_id
                ORDER BY name",
                [':inst_ids' => $inst_ids]
            );
        } else {
            $template->studiengaenge = Studiengang::findBySQL(
                "JOIN news_range ON (mvv_studiengang.studiengang_id = news_range.range_id)
                JOIN news ON (news.news_id = news_range.news_id)
                WHERE Institut_id IN (:inst_ids)
                AND UNIX_TIMESTAMP() <= news.date + expire
                GROUP BY mvv_studiengang.studiengang_id
                ORDER BY name",
                [':inst_ids' => $inst_ids]
            );
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

        $foo_studiengaenge = [];
        foreach (UserStudyCourse::findByUser(User::findCurrent()->id) as $user_studycourse) {
            foreach (Studiengang::findByFachAbschluss($user_studycourse->fach_id, $user_studycourse->abschluss_id) as $stg) {
                $foo_studiengaenge[] = $stg;
            }
        }
        $template->studiengaenge = $foo_studiengaenge;

        $foo = [];
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
     * @param string $studiengang_id
     */
    public function get_entries_action(string $studiengang_id)
    {
        if (!$this->is_admin) {
            return;
        }
        $template = $this->getTemplate('news.php');
        $template->controller = $this;
        $template->studiengang = Studiengang::find($studiengang_id);
        if (
            $GLOBALS['perm']->have_perm('root')
            && !empty($_SESSION['old_studycourse_news'])
        ) {
            $template->news = StudipNews::findBySQL(
                'JOIN news_range USING (news_id)
                  WHERE range_id = :range_id
                  AND :time > news.date + news.expire
                  ORDER BY date DESC, chdate DESC, topic ASC',
                [':time' => time(), ':range_id' => $studiengang_id]
            );
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
        if (!$this->is_admin) {
            return;
        }

        $template = $this->getTemplate('_studycourses');
        $template->studycourses = SimpleCollection::createFromArray(
            Studiengang::findBySQL(
                'institut_id IN (:inst_ids) AND stat = :status ORDER BY abschluss_id, name',
                [':inst_ids' => explode('_', $fk_ids), ':status' => 'genehmigt']
            )
        );

        $news = StudipNews::find($news_id);
        $template->selected_study_courses = $news ? $news->news_ranges->pluck('range_id') : [];
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

        $template->study_courses = SimpleCollection::createFromArray(
            Studiengang::findBySQL(
                'studiengang_id IN (:ids) ORDER BY name',
                [':ids' => $template->entry->news_ranges->pluck('range_id')]
            )
        );
        $template->all_study_courses = SimpleCollection::createFromArray(
            Studiengang::findBySQL(
                'institut_id IN (:ids) ORDER BY name',
                [':ids' => $template->study_courses->pluck('institut_id')]
            )
        );

        echo $template->render();
    }

    public function delete_action ($news_id)
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }
        $news = StudipNews::find($news_id);
        if (!$news) {
            PageLayout::postError(sprintf($this->_('Kein Eintrag mit der ID %s gefunden.'), $news_id));
        } elseif ($news->delete()) {
            PageLayout::postSuccess($this->_('Eintrag erfolgreich gelöscht.'));
        } else {
            PageLayout::postError($this->_('Fehler beim Löschen der Eintrags!'));
        }
        header('Location: ' . URLHelper::getURL('dispatch.php/start'));
    }

    /**
     * Stores an entry.
     * @param null $id
     * @throws AccessDeniedException
     * @throws MethodNotAllowedException
     */
    public function store_action($id = null)
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }

        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $institution_ids = Request::getArray('faculty_id');
        $studycourse_ids = Request::getArray('studycourse_ids');

        if (!$institution_ids || !$studycourse_ids) {
            PageLayout::postError($this->_('Fehler beim Speichern der Ankündigung. Es wurde kein Studiengang ausgewählt.'));
        } else {
            $news = StudipNews::find($id);
            if (!$news) {
                $news = new StudipNews();
                $news->id = $news->getNewId();
            } elseif (!$news->isNew() && $news->user_id != User::findCurrent()->id) {
                $news->chdate_uid = User::findCurrent()->id;
            }
            $news->topic = Request::get('subject');
            $news->body = Studip\Markup::purifyHtml(Request::get('content'));
            $news->user_id = User::findCurrent()->id;
            $news->author = User::findCurrent()->getFullname();
            $news->allow_comments = false;
            $news->date = time();
            $news->expire = strtotime(Request::get('expires') . ' 23:59:59') - $news->date;

            $ranges = [];
            foreach ($studycourse_ids as $studycourse_id) {
                $news_range = NewsRange::find([$news->id, $studycourse_id]);
                if (!$news_range) {
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
        header('Location: ' . URLHelper::getURL('dispatch.php/start'));
    }

    /**
     * Set's 'visited' flag.
     */
    public function visit_action($news_id)
    {
        object_set_visit($news_id, 'news');
        object_add_view($news_id);
        echo object_return_views($news_id);
    }

    /**
     * Shows all currently visible news for a given study course.
     * @throws AccessDeniedException
     */
    public function content_action()
    {
        if (!$this->is_root) {
            throw new AccessDeniedException;
        }
        echo $this->getContent();
    }

    /**
     * @param $template
     * @param bool $layout
     * @return mixed
     */
    protected function getTemplate($template, bool $layout = false): Flexi_Template
    {
        if (Request::isXhr()) {
            header('Content-Type: text/html;charset=utf-8');
            header('X-Initialize-Dialog: true');
        }

        $factory  = new Flexi_TemplateFactory(__DIR__ . '/views');
        $template = $factory->open($template);
        $template->controller = $this;
        $template->_ = function ($string) {
            return $this->_($string);
        };
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
     * @param mixed $to
     */
    public function url_for($to): string
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

    /**
     * @param mixed $to
     */
    public function link_for($to): string
    {
        return htmlReady($this->url_for(...func_get_args()));
    }
}
