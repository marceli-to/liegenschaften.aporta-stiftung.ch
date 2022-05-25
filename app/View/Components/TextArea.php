<?php
namespace App\View\Components;
use Illuminate\View\Component;

class TextArea extends Component
{

  /**
   * Name
   *
   * @var string
   */
  public $name;

  /**
   * Label
   *
   * @var string
   */
  public $label;

  /**
   * Placeholder
   *
   * @var string
   */
  public $placeholder;

  /**
   * Required
   *
   * @var string
   */
  public $required;

  /**
   * Css
   *
   * @var string
   */
  public $css;

  /**
   * Create a new component instance.
   *
   * @param $name
   * @param $type
   * @param $label
   * @param $placeholder
   * @param $required
   * @param $css
   * 
   * @return void
   */
  public function __construct($name, $label, $placeholder = NULL, $required = FALSE, $css = NULL)
  {
    $this->name = $name;
    $this->label = $label;
    $this->placeholder = $placeholder;
    $this->required = $required;
    $this->css = $css;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    return view('components.text-area');
  }
}
