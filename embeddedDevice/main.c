#include "cy_pdl.h"
#include "cyhal.h"
#include "cybsp.h"
/* FreeRTOS header files */
#include "FreeRTOS.h"
#include "task.h"
/* Coinfiguration file for Wi-F */
#include "wifi_config.h"
/* Middleware libraries */
#include "cy_retarget_io.h"
#include "cy_wcm.h"
#include "cy_secure_sockets.h"
#include "cy_http_client_api.h"

#define HTTP_CLIENT_TASK_STACK_SIZE (5 * 1024)
#define HTTP_CLIENT_TASK_PRIORITY (1)

/* This enables RTOS aware debugging. */
volatile int uxTopUsedPriority;

/* HTTP Client task handle. */
TaskHandle_t client_task_handle;

void disconnect_callback(void *arg);
#define REQUEST_BODY "value=69&sensor_id=2"
#define REQUEST_BODY_LENGTH (sizeof(REQUEST_BODY) - 1)
#define BUFFERSIZE (2048 * 2)
#define TEST 2
#if TEST == 1
#define TESTPATH "/anything"
#define SERVERHOSTNAME "httpbin.org"
#elif TEST == 2
#define TESTPATH "/IOT/project/includes/inserting.php"
#define SERVERHOSTNAME "12001510.pxl-ea-ict.be"
#elif TEST == 3
#define TESTPATH "/anything"
#define SERVERHOSTNAME "192.168.1.7"
#endif
#define SERVERPORT (80)
#define SENDRECEIVETIMEOUT (5000)

void wifi_connect(void);

void httpPostRequest(void *arg)
{
    bool connected;

    wifi_connect();

    cy_rslt_t result;
    result = cy_http_client_init();
    if (result != CY_RSLT_SUCCESS)
    {
        printf("HTTP Client Library Initialization Failed!\n\r");
        CY_ASSERT(0);
    }

    cy_awsport_server_info_t serverInfo;
    (void)memset(&serverInfo, 0, sizeof(serverInfo));
    serverInfo.host_name = SERVERHOSTNAME;
    serverInfo.port = SERVERPORT;

    cy_http_disconnect_callback_t disconnectCallback = (void *)disconnect_callback;

    cy_http_client_t clientHandle;
    result = cy_http_client_create(NULL, &serverInfo, disconnectCallback, NULL, &clientHandle);
    if (result != CY_RSLT_SUCCESS)
    {
        printf("HTTP Client Creation Failed!\n\r");
        CY_ASSERT(0);
    }

    result = cy_http_client_connect(clientHandle, SENDRECEIVETIMEOUT, SENDRECEIVETIMEOUT);
    if (result != CY_RSLT_SUCCESS)
    {
        printf("HTTP Client Connection Failed!\n\r");
        CY_ASSERT(0);
    }
    else
    {
        printf("\n\rConnected to HTTP Server Successfully\n\r\n\r");
        connected = true;
    }

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

    result = cy_http_client_write_header(clientHandle, &request, header, num_headers);
    if (result != CY_RSLT_SUCCESS)
    {
        printf("HTTP Client Header Write Failed!\n\r");
        CY_ASSERT(0);
    }
    else
        printf("HTTP Client Header Write succes!\n\r");

    cy_http_client_response_t response;

    if (connected)
    {
        result = cy_http_client_send(clientHandle, &request, (uint8_t *)REQUEST_BODY, REQUEST_BODY_LENGTH, &response);
        if (result != CY_RSLT_SUCCESS)
        {
            printf("HTTP Client Send Failed!\n\r");
            //CY_ASSERT(0);
        }
        else
            printf("HTTP Client Send succes\n\r");
    }

    for (int i = 0; i < response.body_len; i++)
    {
        printf("%c", response.body[i]);
    }
    printf("\n\r");

    while (1)
    {
        vTaskDelay(1);
    }
}

void wifi_connect(void)
{
    cy_rslt_t result;
    cy_wcm_connect_params_t connect_param;
    cy_wcm_ip_address_t ip_address;
    uint32_t retry_count;
    const char *host_name = "12001510.pxl-ea-ict.be";
    cy_socket_ip_address_t ipAddr;

    /* Configure the interface as a Wi-Fi STA (i.e. Client). */
    cy_wcm_config_t config = {.interface = CY_WCM_INTERFACE_TYPE_STA};

    /* Initialize the Wi-Fi Connection Manager and return if the operation fails. */
    result = cy_wcm_init(&config);

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

        // Print Netmask
        printf("Netmask: %d.%d.%d.%d\n\r\r", (uint8_t)connect_param.static_ip_settings->netmask.ip.v4,
               (uint8_t)(connect_param.static_ip_settings->netmask.ip.v4 >> 8), (uint8_t)(connect_param.static_ip_settings->netmask.ip.v4 >> 16),
               (uint8_t)(connect_param.static_ip_settings->netmask.ip.v4 >> 24));

        //Print Gateway
        printf("Gateway: %d.%d.%d.%d\n\r\r", (uint8_t)connect_param.static_ip_settings->gateway.ip.v4,
               (uint8_t)(connect_param.static_ip_settings->gateway.ip.v4 >> 8), (uint8_t)(connect_param.static_ip_settings->gateway.ip.v4 >> 16),
               (uint8_t)(connect_param.static_ip_settings->gateway.ip.v4 >> 24));

        // Print hostname lookup
        cy_socket_gethostbyname(host_name, CY_SOCKET_IP_VER_V4, &ipAddr);
        printf("\"12001510.pxl-ea-ict.be\" IP Address: %d.%d.%d.%d\n\r\r", (uint8_t)ipAddr.ip.v4,
               (uint8_t)(ipAddr.ip.v4 >> 8), (uint8_t)(ipAddr.ip.v4 >> 16),
               (uint8_t)(ipAddr.ip.v4 >> 24));

        // Print MAC Address
        cy_wcm_mac_t MAC_addr;
        cy_wcm_get_mac_addr(CY_WCM_INTERFACE_TYPE_STA, &MAC_addr, 1);
        printf("MAC Address: %X:%X:%X:%X:%X:%X\n\r", MAC_addr[0], MAC_addr[1], MAC_addr[2], MAC_addr[3], MAC_addr[4], MAC_addr[5]);
        break;
    }

    if (result != CY_RSLT_SUCCESS)
        cyhal_gpio_write(CYBSP_USER_LED3, CYBSP_LED_STATE_ON);
    else
        cyhal_gpio_write(CYBSP_USER_LED4, CYBSP_LED_STATE_ON);
}

int main(void)
{

    /* This enables RTOS aware debugging in OpenOCD. */
    uxTopUsedPriority = configMAX_PRIORITIES - 1;

    cybsp_init();
    __enable_irq();

    /* Initialize retarget-io to use the debug UART port. */
    cy_retarget_io_init(CYBSP_DEBUG_UART_TX, CYBSP_DEBUG_UART_RX, CY_RETARGET_IO_BAUDRATE);

    /* Initaize LED pin */
    cyhal_gpio_init(CYBSP_USER_LED3, CYHAL_GPIO_DIR_OUTPUT, CYHAL_GPIO_DRIVE_STRONG, CYBSP_LED_STATE_OFF);
    cyhal_gpio_init(CYBSP_USER_LED4, CYHAL_GPIO_DIR_OUTPUT, CYHAL_GPIO_DRIVE_STRONG, CYBSP_LED_STATE_OFF);

    // Create the wifi_connect task.
    xTaskCreate(httpPostRequest, "postRequest", HTTP_CLIENT_TASK_STACK_SIZE, NULL, HTTP_CLIENT_TASK_PRIORITY, &client_task_handle);

    vTaskStartScheduler();
    /* Never Returns */
}

void disconnect_callback(void *arg)
{
    printf("Disconnected from HTTP Server\n\r");
}
