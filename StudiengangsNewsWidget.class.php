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
    /**
     * StudiengangsNewsWidget constructor.
     */
    public function __construct()
    {
        parent::__construct();

        bindtextdomain(static::GETTEXT_DOMAIN, $this->getPluginPath() . '/locale');
        bind_textdomain_codeset(static::GETTEXT_DOMAIN, 'ISO-8859-1');

        StudipAutoloader::addAutoloadPath($this->getPluginPath() . '/models', 'StudiengangsNews');
        StudipAutoloader::addAutoloadPath($this->getPluginPath() . '/classes', 'StudiengangsNews');

        $faculties = [];

        $this->is_root = $GLOBALS['perm']->have_perm('root');

        // Check for assigned Roles.
        foreach (StudiengangsNews\Config::Get() as $fk_id => $inst_id) {
            if (RolePersistence::isAssignedRole($GLOBALS['user']->user_id, 'fk'.intval($fk_id).'_stgnews')
                || $this->is_root) {
                $faculties[intval($fk_id)] = $inst_id;
            }
        }

        $this->faculties = $faculties;          // All faculties the user is allowed to write news for.
        $this->is_admin  = !empty($faculties);  // True when the user can post news.
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
            $nav = new Navigation('', PluginEngine::getLink($this, [], 'add'));
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

        if($this->is_admin) {
            $abschluss_ids = [];
            $fach_ids = [];
            if(Request::get('study_course_selection', '-') != '-') {
                $exp = explode('_', Request::get('study_course_selection'));
                $abschluss_ids[] = $exp[0];
                $fach_ids[] = $exp[1];
            }
            $study_courses = new StudiengangsNews\StudyCourse(array_keys($this->faculties), $abschluss_ids,
                $fach_ids);
            $widget->content = $this->getContent($study_courses);
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
    protected function getContent(StudiengangsNews\StudyCourse $study_courses)
    {
        $template = $this->getTemplate('widget.php');
        $template->is_admin = $this->is_admin;
        $template->entries = StudiengangsNews\Entry::findByStudyCourses($study_courses);
        $template->selected = Request::get('study_course_selection');
        $template->faculties = $this->faculties;
        $template->study_courses = $study_courses;
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
        $template->entries = StudiengangsNews\Entry::findByStudent($GLOBALS['user']->id);
        return $template->render();
    }

    /**
     * XHR: Fetches the Fachsemester Filter.
     * @param $option
     */
    public function get_fachsemester_action($option)
    {
        $template = $this->getTemplate('_fsfilter.php');
        $template->option = $option;
        echo $template->render();
    }

    /**
     * XHR: Fetches the entries for a given study course.
     * @param $abschl_stg_combo
     */
    public function get_entries_action($abschl_stg_combo = '-')
    {
        if(!$this->is_admin) {
            return;
        }

        $abschluss_ids = [];
        $fach_ids = [];

        // Reminder: Needs two different instances of $study_courses, one for select, one for entries themselves.
        if($abschl_stg_combo != '-' && count(explode('_', $abschl_stg_combo)) == 2) {
            $exp = explode('_', Request::get('study_course_selection'));
            $abschluss_ids[] = $exp[0];
            $fach_ids[]      = $exp[1];
        }
        $template = $this->getTemplate('_admin_select.php');
        $template->faculties = $this->faculties;
        $template->selected = $abschl_stg_combo;
        $template->study_courses = new StudiengangsNews\StudyCourse(array_keys($this->faculties));
        if($abschl_stg_combo == '-') {
            $study_courses = new StudiengangsNews\StudyCourse(array_keys($this->faculties));
        } else {
            $exp =  explode('_', $abschl_stg_combo);
            $study_courses = new StudiengangsNews\StudyCourse(array_keys($this->faculties), [$exp[0]], [$exp[1]]);
        }
        $template->is_admin = $this->is_admin;
        $template->entries = StudiengangsNews\Entry::findByStudyCourses($study_courses);
        echo $template->render();
    }

    /**
     * XHR: Shows degree/subject boxes.
     * @param $path
     * @param $fk_ids
     */
    public function table_action($path, $fk_ids)
    {
        if(!$this->is_admin) {
            return;
        }
        $template = $this->getTemplate('_table.php');
        $template->path = $path;
        $template->fk_ids = $fk_ids;
        $study_courses = new StudiengangsNews\StudyCourse(explode('_', $fk_ids));
        $arr = $study_courses->toArray();
        $template->abschluesse = $arr['abschluesse'];
        $template->faecher = $arr['faecher'];
        $template->selected_faecher = [];
        $template->selected_abschluesse = [];
        echo $template->render();
    }

    /**
     * XHR: Receives the list of valid subjects for a set of degrees.
     * @param string $abschluss_ids
     * @param string $fk_ids
     */
    public function get_faecher_action($abschluss_ids = '', $fk_ids = '')
    {
        if(!$this->is_admin) {
            return;
        }
        $template = $this->getTemplate('_box.php');
        $template->path = 'abschluss';
        $template->selected_faecher = [];
        $template->selected_abschluesse = [];
        $study_courses = new StudiengangsNews\StudyCourse(explode('_', $fk_ids), explode('_', $abschluss_ids));
        $template->faecher = $study_courses->getSubjects();
        echo $template->render();
    }

    /**
     * XHR: Receives the list of valid degrees for a set of subjects.
     * @param string $faecher_ids
     * @param string $fk_ids
     */
    public function get_abschluesse_action($faecher_ids = '', $fk_ids = '')
    {
        if(!$this->is_admin) {
            return;
        }
        $template = $this->getTemplate('_box.php');
        $template->path = 'fach';
        $template->selected_faecher = [];
        $template->selected_abschluesse = [];
        $study_courses = new StudiengangsNews\StudyCourse(explode('_', $fk_ids), [], explode('_', $faecher_ids));
        $template->abschluesse = $study_courses->getDegrees();
        echo $template->render();
    }

    /**
     * XHR: Counts
     */
    public function count_users_action()
    {
        if(!$this->is_admin) {
            return;
        }
        $fach_ids = Request::get('fach_ids');
        $abschluss_ids = Request::get('abschluss_ids');
        $fk_id = Request::get('fk_id');
        $entry = new StudiengangsNews\Entry();
        $entry->fk_id = in_array($fk_id, array_keys($this->faculties))
                      ? ('x000000000000000000000000000000' . intval($fk_id))
                      : 0;
        $entry->fs_qualifier = Request::get('fs_qualifier');
        $entry->fachsemester = Request::get('fachsemester');
        $template = $this->getTemplate('_usercount.php');
        $template->filter = [
            'abschluss_ids' => $abschluss_ids ? explode('_', $abschluss_ids) : [],
            'fach_ids'      => $fach_ids ? explode('_', $fach_ids) : [],
        ];
        $template->entry = $entry;
        echo $template->render();
    }

    /**
     * Creates a new entry.
     * @throws AccessDeniedException
     */
    public function add_action()
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }

        $this->setPageTitle($this->_('Eintrag hinzufügen'));

        $template = $this->getTemplate('edit.php', true);
        $template->entry = new StudiengangsNews\Entry;
        $template->faculties = $this->faculties;

        $template->mode = Request::get('mode', false);

        $template->abschluss_ids = Request::getArray('abschluss_ids', []);
        $template->fach_ids = Request::getArray('fach_ids', []);

        echo $template->render();
    }

    /**
     * Edits an entry.
     * @param $id
     * @throws AccessDeniedException
     */
    public function edit_action($id)
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }

        $this->setPageTitle($this->_('Eintrag bearbeiten'));

        $template = $this->getTemplate('edit.php', true);
        $template->faculties = $this->faculties;
        $template->entry = StudiengangsNews\Entry::find($id);
        $study_courses_filter = $template->entry->getStudyCoursesFilter();
        $template->path = (empty($study_courses_filter['abschluss_ids']))? 'fach' : 'abschluss';
        $template->selected_faecher = $study_courses_filter['fach_ids'];
        $template->selected_abschluesse = $study_courses_filter['abschluss_ids'];
        $template->edit = true;

        if(!in_array(substr($template->entry->fk_id, -1), array_keys($this->faculties))) {
            throw new AccessDeniedException(
                $this->_('Sie können keine Ankündigung für die zugehörige Einrichtung bearbeiten')
            );
        }

        // Grab all values
        $study_courses = new StudiengangsNews\StudyCourse(array_keys($this->faculties));
        $arr = $study_courses->toArray();
        $template->abschluesse = $arr['abschluesse'];
        $template->faecher     = $arr['faecher'];

        // Filter
        if($template->path == 'fach') {
            $study_courses = new StudiengangsNews\StudyCourse(array_keys($this->faculties), [],
                $study_courses_filter['fach_ids']);
            $template->abschluesse = $study_courses->getDegrees();
        } else {
            $study_courses = new StudiengangsNews\StudyCourse(array_keys($this->faculties),
                $study_courses_filter['abschluss_ids']);
            $template->faecher = $study_courses->getSubjects();
        }

        echo $template->render();
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

        $valid = true;
        if (Request::get('path') !== 'abschluss' && Request::get('path') !== 'fach') {
            $valid = false;
            PageLayout::postError($this->_('Bitte wählen Sie den Pfad aus.'));
        }
        if (Request::get('path') === 'abschluss' && !Request::getArray('abschluesse')) {
            $valid = false;
            PageLayout::postError($this->_('Bitte wählen Sie mindestens ein Abschluss aus.'));
        }
        if (Request::get('path') === 'fach' && !Request::getArray('faecher')) {
            $valid = false;
            PageLayout::postError($this->_('Bitte wählen Sie mindestens ein Studiengang aus.'));
        }

        if ($valid) {
            $fach_ids      = Request::getArray('faecher');
            $abschluss_ids = Request::getArray('abschluesse');

            $entry = new StudiengangsNews\Entry($id);
            $entry->fk_id        = StudiengangsNews\Config::mapFacultyNumberToId(Request::int('faculty_id')); // WTF!?
            $entry->subject      = Request::get('subject');
            $entry->fachsemester = Request::get('fachsemester');
            $entry->fs_qualifier = Request::get('fs_qualifier');
            $entry->content      = Request::get('content');
            $entry->user_id      = $GLOBALS['user']->id;
            $entry->expires      = strtotime(Request::get('expires') . ' 23:59:59');
            $entry->activated    = false;
            $entry->store();

            $entry->setStudyCourses($abschluss_ids, $fach_ids);

            PageLayout::postSuccess($this->_('Der Eintrag wurde gespeichert.'));
        }
        header('Location: ' . URLHelper::getLink('dispatch.php/start'));
    }

    /**
     * Set's 'visited' flag.
     */
    public function visit_action()
    {
        $id = Request::option('studiengangsnews-toggle');
        StudiengangsNews\Entry::find($id)->is_new = true;

        header('Content-Type: application/json');
        echo json_encode(true);
    }

    /**
     * Deletes an entry.
     * @param $id
     * @throws AccessDeniedException
     * @throws InvalidMethodException
     */
    public function delete_action($id)
    {
        if (!$this->is_admin) {
            throw new AccessDeniedException();
        }

        if (!Request::isPost()) {
            throw new InvalidMethodException();
        }

        StudiengangsNews\Entry::find($id)->delete();

        PageLayout::postSuccess($this->_('Der Eintrag wurde gelöscht.'));
        header('Location: ' . URLHelper::getLink('dispatch.php/start'));
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
