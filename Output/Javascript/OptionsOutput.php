<?php

namespace CMEN\GoogleChartsBundle\Output\Javascript;

use CMEN\GoogleChartsBundle\GoogleCharts\Options\ChartOptionsInterface;
use CMEN\GoogleChartsBundle\Output\AbstractOptionsOutput;
use CMEN\GoogleChartsBundle\Output\DateOutputInterface;

/**
 * @author Christophe Meneses
 */
class OptionsOutput extends AbstractOptionsOutput
{
    /** @var DateOutputInterface */
    private $dateOutput;

    /**
     * OptionsOutput constructor.
     *
     * @param DateOutputInterface $dateOutput
     */
    public function __construct(DateOutputInterface $dateOutput)
    {
        $this->dateOutput = $dateOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function draw(ChartOptionsInterface $options, $optionsName)
    {
        $this->removeRecursivelyNullValue($options);

        /* @var array $options */
        $this->removeRecursivelyEmptyArray($options);

        $options = $this->renameRecursivelyKeys($options);

        $js = "var $optionsName = {";
        $js .= $this->drawRecursively($options);
        $js .= "};\n";

        return $js;
    }

    public function drawRecursively($options)
    {
        $js = "";
        end($options);
        $lastKey = key($options);
        foreach ($options as $optionKey => $optionValue) {
            $js .= '"'.$optionKey.'":';

            if (isset($optionValue['date'])) {
                $js .= $this->dateOutput->draw(new \DateTime($optionValue['date']));
            } elseif (in_array($optionKey, ['series', 'vAxes'])) {
                $js .= json_encode($optionValue, JSON_FORCE_OBJECT);
            } elseif (is_array($optionValue) && $this->isAssocArray($optionValue)) {
                $js .= "{" . $this->drawRecursively($optionValue) . "}";
            } else {
                $js .= json_encode($optionValue);
            }
            if ($optionKey != $lastKey) {
                $js .= ', ';
            }
        }
        return $js;
    }

    private function isAssocArray(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
