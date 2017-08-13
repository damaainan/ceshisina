<?php
    
    namespace TemplateMethod;
    
    /**
     * CityJourney类（在城市中度假）
     */
    class CityJourney extends Journey
    {
        protected function enjoyVacation()
        {
            echo "Eat, drink, take photos and sleep\n";
        }
    }