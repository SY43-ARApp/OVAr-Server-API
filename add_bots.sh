#!/bin/bash

API_URL="http://chaelpixserver.ddns.net/apis/ovar"
BOT_COUNT=250
NAMES_FILE="names.txt"

echo "Checking HTTP connectivity..."
curl -v --head --silent --fail -L "$API_URL/register.php"
HTTP_STATUS=$?
echo "HTTP status: $HTTP_STATUS"

if [ $HTTP_STATUS -ne 0 ]; then
    API_URL="https://chaelpixserver.ddns.net/apis/ovar"
    echo "Checking HTTPS connectivity..."
    curl -v --head --silent --fail -L "$API_URL/register.php"
    HTTPS_STATUS=$?
    echo "HTTPS status: $HTTPS_STATUS"
    if [ $HTTPS_STATUS -ne 0 ]; then
        echo "ERROR: Cannot reach $API_URL/register.php via HTTP or HTTPS"
        exit 1
    fi
fi

generate_uuid() {
    echo "bot-$(( RANDOM % 100000000 ))"
}

# Clean a name: remove all non-alphanumeric characters
clean_name() {
    echo "$1" | tr -cd '[:alnum:]'
}

# Read all names, shuffle, and use only unique, available ones
mapfile -t ALL_NAMES < <(shuf "$NAMES_FILE")
USED_NAMES=()
COUNT=0

for NAME in "${ALL_NAMES[@]}"; do
    CLEANED_NAME=$(clean_name "$NAME")
    # Skip empty names after cleaning
    if [ -z "$CLEANED_NAME" ]; then
        continue
    fi
    # Check if already used in this script run
    if [[ " ${USED_NAMES[*]} " == *" $CLEANED_NAME "* ]]; then
        continue
    fi
    # Check username availability via API
    AVAIL=$(curl -s "${API_URL}/check_username.php?username=${CLEANED_NAME}")
    if [[ "$AVAIL" == *'"available":true'* ]]; then
        USED_NAMES+=("$CLEANED_NAME")
        UUID=$(generate_uuid)
        SCORE=$(( (RANDOM % 6000) * 10 ))

        REGISTER_URL="${API_URL}/register.php?uuid=${UUID}&username=${CLEANED_NAME}"
        SCORE_URL="${API_URL}/send_score.php?uuid=${UUID}&score=${SCORE}"

        echo "Calling: $REGISTER_URL"
        REGISTER_OUTPUT=$(curl -s -w "\nHTTP_CODE:%{http_code}\n" -L "$REGISTER_URL")
        echo "Register output: $REGISTER_OUTPUT"

        echo "Calling: $SCORE_URL"
        SCORE_OUTPUT=$(curl -s -w "\nHTTP_CODE:%{http_code}\n" -L "$SCORE_URL")
        echo "Score output: $SCORE_OUTPUT"

        echo "Added bot $CLEANED_NAME with UUID $UUID and score $SCORE"

        COUNT=$((COUNT + 1))
        if [ $COUNT -ge $BOT_COUNT ]; then
            break
        fi
    fi
done
