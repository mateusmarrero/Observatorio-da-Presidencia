<?php
declare(strict_types=1);

return [
    'transparencia_api' => [
        'base_url' => getenv('TRANSPARENCIA_API_BASE') ?: 'https://api.portaldatransparencia.gov.br/api-de-dados',
        'key' => getenv('TRANSPARENCIA_API_KEY') ?: '',
        'header_key' => 'chave-api-dados',
        'endpoints' => [
            'cartoes' => '/cartoes',
            'viagens' => '/viagens',
            'despesas' => '/despesas',
            'contratos' => '/contratos',
        ],
    ],
    'bcb_api' => [
        'ipca_url' => getenv('BCB_API_BASE') ?: 'https://api.bcb.gov.br/dados/serie/bcdata.sgs.433/dados',
    ],
    'portais_oficiais' => [
        'portal_transparencia' => 'https://portaldatransparencia.gov.br/',
        'casa_civil_cartoes' => 'https://www.gov.br/casacivil/pt-br/acesso-a-informacao/informacoes-classificadas/despesas-com-cartao-de-pagamento',
        'gsi_diarias_passagens' => 'https://www.gov.br/gsi/pt-br/acesso-a-informacao/receitas-e-despesas/despesas-com-diarias-e-passagens',
        'dados_abertos_download' => 'https://portaldatransparencia.gov.br/download-de-dados/',
    ],
    'orgaos_monitorados' => [
        '20101' => 'Presidência da República',
        '20102' => 'Vice-Presidência da República',
        '20103' => 'Gabinete de Segurança Institucional (GSI)',
        '20104' => 'Casa Civil da Presidência da República',
        '20105' => 'Secretaria-Geral da Presidência da República',
        '20106' => 'Secretaria de Comunicação Social (SECOM)',
    ],
];
