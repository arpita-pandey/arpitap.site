#!/bin/bash
# alert-check.sh - Analytics alerting script
# Run via cron every 15 minutes:
# */15 * * * * /var/www/reporting.arpitap.site/alert-check.sh >> /var/log/analytics-alerts.log 2>&1

DB_USER="analytics_user"
DB_PASS="NewStrongPass123!"
DB_NAME="analytics"

THRESHOLD_ERRORS=50
THRESHOLD_LOAD_MS=5000
ALERT_LOG="/var/log/analytics-alerts.log"

# Count errors in the last 15 minutes
ERROR_COUNT=$(mysql -u"$DB_USER" -p"$DB_PASS" -N -e "
    SELECT COUNT(*) FROM events
    WHERE event_type = 'error'
    AND created_at > NOW() - INTERVAL 15 MINUTE
" "$DB_NAME" 2>/dev/null)

# Average load time in the last 15 minutes
AVG_LOAD=$(mysql -u"$DB_USER" -p"$DB_PASS" -N -e "
    SELECT COALESCE(ROUND(AVG(JSON_EXTRACT(data, '$.totalLoadTime'))), 0) FROM events
    WHERE event_type = 'performance'
    AND created_at > NOW() - INTERVAL 15 MINUTE
" "$DB_NAME" 2>/dev/null)

# Zero traffic check (no pageviews in 30 min)
RECENT_PV=$(mysql -u"$DB_USER" -p"$DB_PASS" -N -e "
    SELECT COUNT(*) FROM events
    WHERE event_type = 'page_enter'
    AND created_at > NOW() - INTERVAL 30 MINUTE
" "$DB_NAME" 2>/dev/null)

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Check thresholds and log alerts
if [ "${ERROR_COUNT:-0}" -gt "$THRESHOLD_ERRORS" ]; then
    echo "[$TIMESTAMP] ALERT: $ERROR_COUNT errors in last 15 min (threshold: $THRESHOLD_ERRORS)"
    # Uncomment to send to Slack:
    # curl -sX POST "$SLACK_WEBHOOK" -d "{\"text\": \"ALERT: $ERROR_COUNT errors in last 15 min\"}"
fi

if [ "${AVG_LOAD:-0}" -gt "$THRESHOLD_LOAD_MS" ]; then
    echo "[$TIMESTAMP] ALERT: Avg load time ${AVG_LOAD}ms in last 15 min (threshold: ${THRESHOLD_LOAD_MS}ms)"
fi

if [ "${RECENT_PV:-0}" -eq "0" ]; then
    HOUR=$(date +%H)
    # Only alert during business hours (8am-10pm)
    if [ "$HOUR" -ge 8 ] && [ "$HOUR" -le 22 ]; then
        echo "[$TIMESTAMP] ALERT: Zero pageviews in last 30 minutes during business hours"
    fi
fi

# Normal status (no alerts)
if [ "${ERROR_COUNT:-0}" -le "$THRESHOLD_ERRORS" ] && [ "${AVG_LOAD:-0}" -le "$THRESHOLD_LOAD_MS" ]; then
    echo "[$TIMESTAMP] OK: errors=$ERROR_COUNT, avg_load=${AVG_LOAD}ms, recent_pv=$RECENT_PV"
fi
