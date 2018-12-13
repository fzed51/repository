<?php

namespace Repository;

interface ValidatorInterface
{
    /**
     * Validation d'une entity
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function __invoke(array $data);
}