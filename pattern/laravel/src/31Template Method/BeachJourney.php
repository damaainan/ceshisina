<?php
    
    namespace TemplateMethod;
    
    /**
     * BeachJourney类（在海滩度假）
     */
    class BeachJourney extends Journey
    {
        protected function enjoyVacation()
        {
            echo "Swimming and sun-bathing\n";
        }
    }