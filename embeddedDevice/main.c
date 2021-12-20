#include "main.h"
#include "DHT_task.h"
#include "httpClient.h"

struct readings lastHttpValue = {0, 0};

void interruptTemp(void *callback_arg, cyhal_timer_event_t event)
{
    sensor_type = 1;
    xTaskResumeFromISR(httpSend_task_handle);
}

void interruptHumi(void *handler_arg, cyhal_gpio_irq_event_t event)
{
    sensor_type = 2;
    xTaskResumeFromISR(httpSend_task_handle);
}

void softwareTimer(void *arg)
{
    for (;;)
    {
        //every hour send temp on first half hour and humi and second half hour
        vTaskDelay(pdMS_TO_TICKS(1800000));
        printf("\r\nLeetsgoow\r\n");
        sensor_type = 1; //temp
        vTaskResume(httpSend_task_handle);
        vTaskDelay(pdMS_TO_TICKS(1800000));
        sensor_type = 2; //humi
        vTaskResume(httpSend_task_handle);
    }
}

float Fraction_Convert(uint8_t num)
{
    float fraction = 0;
    int unit = 0;
    for (int i = 0; i < 8; i++)
    {
        unit = num & 1;
        num = num >> 1;
        fraction = fraction + unit * pow(2, -(1 + i));
    }
    return fraction;
}

void DHT_Start(void)
{
    cyhal_gpio_write(DATA_PIN, 1);
    cyhal_system_delay_ms(1000);
    cyhal_gpio_write(DATA_PIN, 0);
    cyhal_system_delay_ms(18);
    cyhal_gpio_write(DATA_PIN, 1);
}

uint8 DHT_Read(float *humidity, float *temperature)
{
    uint8_t delay_time = 0, ack_time = 0;
    uint8_t temp = 0, index = 0, bit_count = 7;
    uint8_t byteval[5] = {0, 0, 0, 0, 0}; // Array to store the 5 byte values received from the sensor

    DHT_Start();
    taskENTER_CRITICAL();
    while (cyhal_gpio_read(DATA_PIN) == 1)
    {
        cyhal_system_delay_us(1);
        ack_time++;
        if (ack_time > timeout_duration)
            return DHT_CONNECTION_ERROR;
    }

    while (cyhal_gpio_read(DATA_PIN) == 0)
    {
        cyhal_system_delay_us(1);
        ack_time++;
        if (ack_time > timeout_duration)
            return DHT_CONNECTION_ERROR;
    }

    delay_time = ack_time;
    ack_time = 0;
    while (cyhal_gpio_read(DATA_PIN) == 1)
    {
        /* Spin until sensor pulls the data line high */
        cyhal_system_delay_us(1);
        ack_time++;
        if (ack_time > timeout_duration)
            return DHT_CONNECTION_ERROR;
    }

    for (int i = 0; i < 40; i++)
    {
        ack_time = 0;
        while (cyhal_gpio_read(DATA_PIN) == 0)
        {
            /* Spin until sensor pulls the data line high */
            cyhal_system_delay_us(1);
            ack_time++;
            if (ack_time > timeout_duration)
                return DHT_CONNECTION_ERROR;
        }

        ack_time = 0;

        while (cyhal_gpio_read(DATA_PIN) == 1)
        {
            cyhal_system_delay_us(1);
            ack_time++;
            if (ack_time > timeout_duration)
                return DHT_CONNECTION_ERROR;
        }

        if ((ack_time) > (delay_time / 2))
            byteval[index] |= (1 << bit_count);
        if (bit_count == 0) /* Next Byte */
        {
            bit_count = 7; /* Reset bit_count to 7 */
            index++;       /* Increment index so that next byte is chosen */
        }
        else
            bit_count--;
    }
    taskEXIT_CRITICAL();

    /* Checksum is calculated by adding all 4 bytes */
    temp = (byteval[0] + byteval[1] + byteval[2] + byteval[3]);
    if ((temp == byteval[4]) && (byteval[4] != 0))
    {
        *humidity = (int)byteval[0] + Fraction_Convert(byteval[1]);
        *temperature = (int)byteval[2] + Fraction_Convert(byteval[3]);
        return SUCCESS;
    }
    else
    {
        taskENTER_CRITICAL();
        return DHT_INCORRECT_VALUE;
    }
}

void sampleSensor(void *arg)
{
    struct readings DHT_reading = {0, 0};
    for (;;)
    {
        vTaskDelay(pdMS_TO_TICKS(20000));
        DHT_Read(&DHT_reading.humidity, &DHT_reading.temperature);
        if ((lastHttpValue.temperature > (DHT_reading.temperature + 1)) || (lastHttpValue.temperature < (DHT_reading.temperature - 1)))
        {
            printf("before temp: %f, now temp: %f\r\n", lastHttpValue.temperature, DHT_reading.temperature);
            vTaskDelay(pdMS_TO_TICKS(5000));
            sensor_type = 1;
            vTaskResume(httpSend_task_handle);
            continue;
        }
        if ((lastHttpValue.humidity > (DHT_reading.humidity + 5)) || (lastHttpValue.humidity < (DHT_reading.humidity - 5)))
        {
            printf("before humi: %f, now humi: %f\r\n", lastHttpValue.humidity, DHT_reading.humidity);
            vTaskDelay(pdMS_TO_TICKS(3000));
            sensor_type = 2;
            vTaskResume(httpSend_task_handle);
            continue;
        }
        else
        {
            printf("\r\nNo change\r\n");
        }
    }
}

void httpSend(void *pvParameters)
{
    cy_rslt_t result;
    struct readings DHT_reading = {0, 0};
    cy_awsport_server_info_t serverInfo;
    (void)memset(&serverInfo, 0, sizeof(serverInfo));
    serverInfo.host_name = SERVERHOSTNAME;
    serverInfo.port = SERVERPORT;

    cy_http_disconnect_callback_t disconnectCallback = (void *)disconnect_callback;
    cy_http_client_t clientHandle;

    uint8_t buffer[BUFFERSIZE];
    cy_http_client_request_header_t request;
    request.buffer = buffer;
    request.buffer_len = BUFFERSIZE;
    request.method = CY_HTTP_CLIENT_METHOD_POST;
    request.range_start = -1;
    request.range_end = -1;
    request.resource_path = TESTPATH;

    uint8_t num_headers = 1;
    cy_http_client_header_t header[num_headers];
    header[0].field = "Content-Type";
    header[0].field_len = strlen("Content-Type");
    header[0].value = "application/x-www-form-urlencoded";
    header[0].value_len = strlen("application/x-www-form-urlencoded");

    result = cy_http_client_init();
    if (result != CY_RSLT_SUCCESS)
        printf("HTTP Client Library Initialization Failed!\n\r");

    result = cy_http_client_create(NULL, &serverInfo, disconnectCallback, NULL, &clientHandle);
    if (result != CY_RSLT_SUCCESS)
        printf("HTTP Client Creation Failed!\n\r");

    for (;;)
    {
        //suspending task and waiting for interrupt to resume
        vTaskSuspend(httpSend_task_handle);
        //reading sensorValues
        DHT_reading.result_code = DHT_Read(&DHT_reading.humidity, &DHT_reading.temperature);
        result = cy_http_client_write_header(clientHandle, &request, header, num_headers);
        if (result != CY_RSLT_SUCCESS)
            printf("HTTP Client Header Write Failed!\n\r");

        result = cy_http_client_connect(clientHandle, SENDRECEIVETIMEOUT, SENDRECEIVETIMEOUT);
        if (result != CY_RSLT_SUCCESS)
            printf("HTTP Client Connection Failed!\n\r");
        else
            connected = true;

        char front[30] = "value=";
        if (sensor_type == 1)
        {
            snprintf(value, (sizeof(value) - 1), "%.3f", DHT_reading.temperature);
            strcat(front, value);
            strcat(front, end);
            strcat(front, "1");
            lastHttpValue.temperature = DHT_reading.temperature;
        }
        else if (sensor_type == 2)
        {
            snprintf(value, (sizeof(value) - 1), "%.3f", DHT_reading.humidity);
            strcat(front, value);
            strcat(front, end);
            strcat(front, "2");
            lastHttpValue.humidity = DHT_reading.humidity;
        }

        printf("request body: %s\r\n", front);
        sensor_type = 0;

        if (connected)
        {
            result = cy_http_client_send(clientHandle, &request, front, strlen(front), &response);
            if (result != CY_RSLT_SUCCESS)
                printf("HTTP Client Send Failed!\n\r");
            else
                printf("HTTP Client Send succes\n\r");
        }

        result = cy_http_client_disconnect(clientHandle);
        if (result != CY_RSLT_SUCCESS)
            printf("HTTP Client disconnect Failed!\n\r");
    }
}

void wifi_connect(void *arg)
{
    cy_rslt_t result;
    cy_wcm_connect_params_t connect_param;
    cy_wcm_ip_address_t ip_address;
    uint32_t retry_count;

    /* Configure the interface as a Wi-Fi STA (i.e. Client). */
    cy_wcm_config_t config = {.interface = CY_WCM_INTERFACE_TYPE_STA};

    /* Initialize the Wi-Fi Connection Manager and return if the operation fails. */
    result = cy_wcm_init(&config);
    if (result != CY_RSLT_SUCCESS)
        printf("\r\nWi-Fi Connection Manager initialization failed\r\n");
    else
        printf("\n\rWi-Fi Connection Manager initialized.\n\r\r");

    /* Configure the connection parameters for the Wi-Fi interface. */
    memset(&connect_param, 0, sizeof(cy_wcm_connect_params_t));
    memcpy(connect_param.ap_credentials.SSID, WIFI_SSID, sizeof(WIFI_SSID));
    memcpy(connect_param.ap_credentials.password, WIFI_PASSWORD, sizeof(WIFI_PASSWORD));
    connect_param.ap_credentials.security = WIFI_SECURITY;

    /* Connect to the Wi-Fi AP. */
    for (retry_count = 0; retry_count < MAX_WIFI_CONN_RETRIES; retry_count++)
    {
        printf("Connecting to Wi-Fi AP '%s'\n\r\r", connect_param.ap_credentials.SSID);
        result = cy_wcm_connect_ap(&connect_param, &ip_address);

        if (result != CY_RSLT_SUCCESS)
            continue;
        printf("Successfully connected to Wi-Fi network '%s'\n\r\r", connect_param.ap_credentials.SSID);
        if (ip_address.version == CY_WCM_IP_VER_V4)
            printf("My ipv4: %d.%d.%d.%d\n\r\r", (uint8_t)ip_address.ip.v4, (uint8_t)(ip_address.ip.v4 >> 8), (uint8_t)(ip_address.ip.v4 >> 16), (uint8_t)(ip_address.ip.v4 >> 24));
        else
            printf("My ipv6: %0X.%0X.%0X.%0X\n\r\r", (unsigned int)ip_address.ip.v6[0], (unsigned int)ip_address.ip.v6[1], (unsigned int)ip_address.ip.v6[2], (unsigned int)ip_address.ip.v6[3]);
        break;
    }
    if (result != CY_RSLT_SUCCESS)
    {
        printf("\r\nWifi connection failed.\r\n");
        CY_ASSERT(0);
    }
    else
    {
        while (1)
        {
            if (cy_wcm_is_connected_to_ap() != true)
            {
                cyhal_gpio_write(CYBSP_LED8, CYBSP_LED_STATE_ON);
                break;
            }
            vTaskDelay(10000);
        }
    }
}

int main(void)
{
    cybsp_init();

    cy_retarget_io_init(CYBSP_DEBUG_UART_TX, CYBSP_DEBUG_UART_RX, CY_RETARGET_IO_BAUDRATE);
    cyhal_gpio_init(CYBSP_LED8, CYHAL_GPIO_DIR_OUTPUT, CYHAL_GPIO_DRIVE_STRONG, CYBSP_LED_STATE_OFF);
    cyhal_gpio_init(CYBSP_LED_RGB_BLUE, CYHAL_GPIO_DIR_OUTPUT, CYHAL_GPIO_DRIVE_STRONG, CYBSP_LED_STATE_OFF);
    cyhal_gpio_init(DATA_PIN, CYHAL_GPIO_DIR_BIDIRECTIONAL, CYHAL_GPIO_DRIVE_PULLUP, 1);
    cyhal_gpio_init(CYBSP_SW2, CYHAL_GPIO_DIR_INPUT, CYHAL_GPIO_DRIVE_NONE, false);
    cyhal_gpio_init(CYBSP_D9, CYHAL_GPIO_DIR_INPUT, CYHAL_GPIO_DRIVE_NONE, false);

    cyhal_gpio_register_callback(CYBSP_SW2, interruptTemp, NULL);
    cyhal_gpio_enable_event(CYBSP_SW2, CYHAL_GPIO_IRQ_FALL, 5, true);
    cyhal_gpio_register_callback(CYBSP_D9, interruptHumi, NULL);
    cyhal_gpio_enable_event(CYBSP_D9, CYHAL_GPIO_IRQ_FALL, 5, true);

    xTaskCreate(wifi_connect, "wifi_connect_task", 1024, NULL, 2, NULL);
    xTaskCreate(httpSend, "SendHttp_task", HTTP_CLIENT_TASK_STACK_SIZE, NULL, 5, &httpSend_task_handle);
    xTaskCreate(softwareTimer, "softwareTimer_task", 1024, NULL, 4, NULL);
    xTaskCreate(sampleSensor, "sampleSensor_task", 1024, NULL, 3, NULL);

    vTaskStartScheduler();
}

void disconnect_callback(void *arg)
{
    printf("Disconnected from HTTP Server\n\r");
}