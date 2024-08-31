$prompt = @(
"Select command (type a number)`n"
"1 - docker-restart`n"
"2 - docker-stop`n"
"3 - docker-run`n"
"4 - docker-rebuild`n"
"5 - docker-rebuild php"
) -join ' '

$operation = Read-Host $prompt

if ($operation -eq '1')
{
    docker compose down
    docker compose up -d
}

if ($operation -eq '2')
{
    docker compose down
}

if ($operation -eq '3')
{
    docker compose up -d
}

if ($operation -eq '4')
{
    docker compose down
    docker compose build
    docker compose up -d
}

if ($operation -eq '5')
{
    docker compose down
    docker compose build php-8.0
    docker compose up -d
}

Start-Sleep 1