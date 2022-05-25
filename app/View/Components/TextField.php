<?php
namespace App\View\Components;
use Illuminate\View\Component;

class TextField extends Component
{

  /**
   * Name
   *
   * @var string
   */
  public $name;

  /**
   * Type
   *
   * @var string
   */
  public $type;

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
   * Autocomplete
   *
   * @var boolean
   */
  public $autocomplete;

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
  public function __construct($name, $type = 'text', $label = NULL, $placeholder = NULL, $required = FALSE, $css = NULL, $autocomplete = TRUE)
  {
    $this->name = $name;
    $this->type = $type;
    $this->label = $label;
    $this->placeholder = $placeholder;
    $this->required = $required;
    $this->css = $css;
    $this->autocomplete = $autocomplete;
  }

  /**
   * Get the view / contents that represent the component.
   *
   * @return \Illuminate\View\View|string
   */
  public function render()
  {
    return view('components.text-field');
  }
}
