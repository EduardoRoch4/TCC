# API de Notícias Financeiras

## Configuração

### Opção 1: NewsAPI (Recomendado)

1. Acesse https://newsapi.org/ e crie uma conta gratuita
2. Obtenha sua chave API
3. Edite o arquivo `api/noticias.php` e substitua `YOUR_NEWSAPI_KEY_HERE` pela sua chave:

```php
$newsApiKey = 'sua_chave_aqui';
```

### Opção 2: RSS Feeds (Fallback Automático)

Se você não configurar a NewsAPI, o sistema usará automaticamente feeds RSS de sites brasileiros de finanças:
- InfoMoney
- Valor Econômico
- G1 Economia
- Estadão Economia

## Uso

A API está disponível em: `api/noticias.php`

Retorna JSON com o formato:
```json
{
  "noticias": [
    {
      "titulo": "Título da notícia",
      "descricao": "Descrição...",
      "link": "https://...",
      "data": "2024-01-01 12:00:00",
      "imagem": "https://...",
      "fonte": "Nome do site"
    }
  ]
}
```

## Notas

- A API filtra automaticamente apenas notícias relacionadas a finanças
- Limita a 12 notícias por requisição
- Ordena por data (mais recentes primeiro)
