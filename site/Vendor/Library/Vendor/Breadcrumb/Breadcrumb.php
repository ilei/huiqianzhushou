<?php
/**
 * Breadcrumb Class
 *
 *
 * @version     1.0
 * @author      wangleiming<wangleiming@yunmai365.com>
 */
class Breadcrumb {
    
    /**
     * Breadcrumbs stack
     *
     */
    private $breadcrumbs    = array();
    
    /**
     * Options
     *
     */
    private $_divider       = '>';
    private $_tag_open      = '<h5> <strong>当前位置：&nbsp;</strong>';
    private $_tag_close     = '</h5>';
    
    /**
     * Constructor
     *
     * @access  public
     * @param   array   initialization parameters
     */
    public function __construct($params = array()){
        if (count($params) > 0)
        {
            $this->initialize($params);
        }
        
    }


    /**
     * Initialize Preferences
     *
     * @access  public
     * @param   array   initialization parameters
     * @return  void
     */
    private function initialize($params = array()){
        if (count($params) > 0)
        {
            foreach ($params as $key => $val)
            {
                if (isset($this->{'_' . $key}))
                {
                    $this->{'_' . $key} = $val;
                }
            }
        }
    }
    
    // --------------------------------------------------------------------

    /**
     * Append crumb to stack
     *
     * @access  public
     * @param   string $title
     * @param   string $href
     * @return  void
     */     
    function append_crumb($title, $href){
        // no title or href provided
        if (!$title) return;
        
        // add to end
        $this->breadcrumbs[] = array('title' => $title, 'href' => $href);
    }
    
    // --------------------------------------------------------------------

    /**
     * Prepend crumb to stack
     *
     * @access  public
     * @param   string $title
     * @param   string $href
     * @return  void
     */     
    function prepend_crumb($title, $href)
    {
        // no title or href provided
        if (!$title) return;
        
        // add to start
        array_unshift($this->breadcrumbs, array('title' => $title, 'href' => $href));
    }
    
    // --------------------------------------------------------------------

    /**
     * Generate breadcrumb
     *
     * @access  public
     * @return  string
     */     
    function output()
    {
        // breadcrumb found
        if ($this->breadcrumbs) {
        
            // set output variable
            $output = $this->_tag_open;
            
            // add html to output
            foreach ($this->breadcrumbs as $key => $crumb) {
                
                // add divider
                if ($key) $output .= $this->_divider;
                if($crumb['href']){
                    $output .= '<a href="' . $crumb['href'] . '">&nbsp;' . $crumb['title'] . '&nbsp;</a>';
                }else{
                    $output .= '&nbsp;' . $crumb['title'] . '&nbsp;';
                } 
            }
            
            // return html
            return $output . $this->_tag_close . PHP_EOL;
        }
        
        return '';
    }

}
